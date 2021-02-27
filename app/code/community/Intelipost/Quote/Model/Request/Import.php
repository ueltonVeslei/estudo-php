<?php

class Intelipost_Quote_Model_Request_Import
// extends Varien_Object
{

	public $_shipping_price;
	public $_number_of_days;
	public $_imported = false;

public function requestImport($weight, $destZipCode)
{
	$this->_shipping_price = ''; // defining shipping price
	$this->_number_of_days = ''; // defining number_of_days to deliver

	$root_dir_path = Mage::getBaseDir();
	$media_dir_path = $root_dir_path.DIRECTORY_SEPARATOR.'media';

	$intelipost_dir_path = $media_dir_path.DIRECTORY_SEPARATOR.'intelipost';
	if (!is_dir($intelipost_dir_path)) mkdir ($intelipost_dir_path, 0777, true);

	$filepath = $intelipost_dir_path.DIRECTORY_SEPARATOR."state_codification.json";
	touch ($filepath);

	if (file_exists($filepath))
	{ // check if file exists locally

		$weightUnit = Mage::getStoreConfig ('intelipost_basic/settings/weight_unit');
		$c_state = ''; $c_type = ''; // defining empty variables for state and type

		$intZipCode = (int)$destZipCode; // Transform ZIP code from string => numeric

		$c_weight = $weightUnit == 'kg' ? $weight*1000 : $weight; // converting total weight of quote into grams

		$state_codification = json_decode(file_get_contents($filepath)); // load state_codification.json as array

		$intArray = array(); // Defining a new array for integer values

        if (count($state_codification)) {
            foreach ($state_codification[0] as $key => $value) {
                $intArray[(int)$key] = $value; // Transform keys of array from string => numeric
            }
        }
		asort($intArray); // Sort the keys of the array ascending

		foreach($intArray as $key => $value)
		{
			if(($intZipCode > $key) && ($intZipCode < (int)$value->cep_end))
			{
				$c_state = trim($value->state); // assigning value of state here if found
				$c_type = ucfirst(strtolower($value->type)); // assigning value of type here if found
				break;
			}
		}

		if($c_state != '' && $c_type != '')
		{ // if state and type are found
			$contingencyTable = Mage::getStoreConfig ('carriers/intelipost/table_name');
			$contingencyTable = strpos($contingencyTable, '.json') ? $contingencyTable : $contingencyTable . '.json';

			$filepath = $intelipost_dir_path.DIRECTORY_SEPARATOR.$contingencyTable;


			if (file_exists($filepath))
			{ // check if file exists locally
				$esedex = json_decode(file_get_contents($filepath)); // Load configured backup table: e.g. esedex.sp.json

				$this->_number_of_days = $esedex->$c_state->$c_type->delivery_estimate_business_days;

				foreach($esedex->$c_state->$c_type->final_shipping_cost as $key => $value)
				{
					if(($key > $c_weight) && !isset($last_v))
					{
						$this->_shipping_price = $value;
						break;
					}

					if($key > $c_weight)
					{
						$this->_shipping_price = $last_v;
						break;
					}

					$last_k = $key; // saving -1 key
					$last_v = $value; // saving value for -1 key
				}
			}

		}

		if($this->_shipping_price != '' && $this->_number_of_days != '')
		{
			$this->_imported = true;
		}

	}

	return $this->_imported;
}

}

