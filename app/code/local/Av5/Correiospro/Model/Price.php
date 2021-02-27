<?php
class Av5_Correiospro_Model_Price extends Mage_Core_Model_Abstract {
	
	protected $_request;
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('av5_correiospro/price');
	}
	
	public function getUpdateCollection($state=null, $frequency, $limit) {
		$collection = $this->getCollection()
			->addFieldToFilter(array('updated_at','updated_at'),array(
					array('lteq' => date('Y-m-d', strtotime('-'.$frequency.' days'))),
					array('null' => true)
			)
		)
        ->addFieldToFilter('region',811);
		
		$collection->getSelect()
			->join(array('location' => Mage::getSingleton('core/resource')->getTableName('av5_correiospro/location')),'main_table.region = location.id',array('location.start','location.end'));
			
		if (is_string($state) && !empty($state)) {
			$collection->addFieldToFilter('location.state',array('like' => $state));
		}
	
		if ($limit) {
			$collection->setPageSize($limit);
		}

		if (Mage::getSingleton('core/session')->getCorreiosPage()) {
			$collection->setCurPage(Mage::getSingleton('core/session')->getCorreiosPage());
		}

		
		return $collection;
	}
	
	public function getRates(Mage_Shipping_Model_Rate_Request $request) {
		$this->_request = $request;
		$collection = $this->getCollection();
		
		$collection->getSelect()
			->join(array('location' => Mage::getSingleton('core/resource')->getTableName('av5_correiospro/location')),'main_table.region = location.id',array('location.start','location.end'));
		
		$collection->addFieldToFilter('location.start',array('lteq' => Mage::helper('av5_correiospro')->_formatZip($this->_request->getDestPostcode())))
			->addFieldToFilter('location.end',array('gteq' => Mage::helper('av5_correiospro')->_formatZip($this->_request->getDestPostcode())));

		Mage::helper('av5_correiospro')->log('SQL: ' . $collection->getSelect()->assemble());
		
		return $this->_processRates($collection);
	}
	
	protected function _processRates($collection) {
		$return = array();
		$hasInsurance = Mage::helper('av5_correiospro')->getConfigData('declared_value');
		$weightPkg = $this->_request->getFixedPackageWeight();
		$services = $this->_request->getPostingMethods();
		
		if ($weightPkg <= 0.3) {
            $weightPkg = '0.3';
		} elseif ($weightPkg <= 0.5) {
            $weightPkg = '0.5';
		} else {
            $weightPkg = ceil($weightPkg);
		}
		
		foreach ($collection as $rate) {
			$rates = json_decode($rate->getPrices(),true);
			if ($hasInsurance) {
				$insurances = json_decode($rate->getDeclared(),true);
			}
			if (is_array($rates)) {
				foreach ($services as $service) {
                    $minWeight = Mage::helper('av5_correiospro')->getConfigData('serv_' . $service . '/minweight');
                    $weight = $weightPkg;
                    if ($minWeight > $weight) {
                        $weight = $minWeight;
                    }

					if (isset($rates[$service][$weight])) {
						$value = $rates[$service][$weight]['vl'];
						$value += $rates[$service][$weight]['ar'];
						$value += $rates[$service][$weight]['mp'];
						if ($hasInsurance) {
							$insuranceValue = $insurances[$service][$this->_getInsuranceRate($this->_request->getPackageValue())];
							if (!isset($insuranceValue))
								$value += $insuranceValue;
						}
						$return[] = array(
							'servico'		=> $service,
							'peso'			=> $weight,
							'valor'			=> $value,
							'prazo'			=> $rates[$service][$weight]['dl'],
							'areas_risco'	=> $rate->getRisk(),
						);
					}
				}
			}
		}
		
		return $return;
	}
	
	protected function _getInsuranceRate($value) {
		if ($value < 100) {
			$value = 100;
		} elseif($value < 500) {
			$value = 500;
		} else {
			$value = ceil($value/500) * 500;
		}
		
		return $value;
	}
	
	public function update($data) {
		$location = Mage::getModel('av5_correiospro/location')->getCollection()
			->addFieldToFilter('start',array('lteq' => $data['zipcode']))
			->addFieldToFilter('end',array('gteq' => $data['zipcode']))
			->getFirstItem();
		
		if ($location) {
			$priceData = array(
				'vl'		=> $data['valor_lq'],
				'ar'		=> $data['valor_ar'],
				'mp'		=> $data['valor_mp'],
				'dl'		=> $data['prazo'],
			);
			$price = $this->load($location->getId(), 'region');
			if ($price) {
				$prices = json_decode($price->getPrices(),true);
				if (!is_array($prices)) {
					$prices = array();
				}
				
				$insurance = json_decode($price->getDeclared(),true);
			} else {
				$price = $this;
				$prices = array();
				$insurance = array();
				
				$price->setRegion($location->getId());
				$price->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
				$price->setStoreId(Mage::app()->getStore()->getStoreId());
			}
			
			$prices[$data['servico']][(string)$data['weight']] = $priceData;
			
			if ($data['valor_sg']) {
				$insurance[$data['servico']][(string)$this->_getInsuranceRate($data['valor_pct'])] = $data['valor_sg'];
			}
			
			$price->setPrices(json_encode($prices));
			$price->setDeclared(json_encode($insurance));
			$price->setRisk($data['areas_risco']);
			$price->setUpdatedAt(date('Y-m-d H:i:s'));

			if (!$price->getId()) {
                Mage::log('LOCATION: ' . $location->getId() . ' - ZIPCODE: ' . $data['zipcode'] . ' - WEIGHT: ' . $data['weight'],null,'correiosupdate.log');
            }

			$price->save();
		}
	}
	
	public function exclude($zipcode) {
		$location = Mage::getModel('av5_correiospro/location')->getCollection()
			->addFieldToFilter('start',array('lteq' => $zipcode))
			->addFieldToFilter('end',array('gteq' => $zipcode))
			->getFirstItem();
		
		if ($location) {
			$price = $this->load($location->getId(), 'region');
			if ($price) {
				$price->delete();
				$location = Mage::getModel('av5_correiospro/location')->load($location->getId());
				$location->delete();
			}
		}
	}
	
	public function getOutdated($state=null) {
		$collection = $this->getCollection()
			->addFieldToFilter(array('updated_at','updated_at'),array(
					array('lteq' => date('Y-m-d', strtotime('-'.Mage::helper('av5_correiospro')->getConfigData('update_frequency').' days'))),
					array('null' => true)
			));
		if ($state) {
			$collection->getSelect()
				->join(array('location' => Mage::getSingleton('core/resource')->getTableName('av5_correiospro/location')),'main_table.region = location.id',array());
			$collection->addFieldToFilter('location.state',array('like' => $state));
		}
		
		return $collection->getSize();
	}
	
	public function getUpdated($state=null) {
		$collection = $this->getCollection()
			->addFieldToFilter('updated_at',array('gteq' => date('Y-m-d', strtotime('-'.Mage::helper('av5_correiospro')->getConfigData('update_frequency').' days'))));
		if ($state) {
			$collection->getSelect()
				->join(array('location' => Mage::getSingleton('core/resource')->getTableName('av5_correiospro/location')),'main_table.region = location.id',array());
			$collection->addFieldToFilter('location.state',array('like' => $state));
		}
	
		return $collection->getSize();
	}
	
}
