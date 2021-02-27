<?php
/**
 * Onestic - Smart PBMs
 *
 * @title      Magento -> Módulo Smart PBMs
 * @category   Integração
 * @package    Onestic_Smartpbm
 * @author     Onestic
 * @copyright  Copyright (c) 2020 Onestic
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Smartpbm_Model_Pbms_Funcional extends Onestic_Smartpbm_Model_Pbms_Abstract {

    protected $_name = 'funcional';
    protected $_label = 'Funcional';
    protected $_login;
    protected $_password;
    protected $_cnpj;
    protected $_crf;
    protected $_ufcrf;
    protected $_tipo;
    protected $_simulacao;
    protected $_apiKey;
    protected $_clientType = 'REST';

    protected $_urlP = 'https://proxy.funcionalmais.com/AutorizadorOmniChannel/REST';
    protected $_urlD = 'http://acessohml.funcionalmais.com/AutorizadorOmniChannelExterno/REST';

	public function __construct() {
        $this->_environment = Mage::helper('smartpbm')->getConfigData('funcional/environment');
        $this->_url = $this->{'_url' . $this->_environment};
        $this->_login = Mage::helper('smartpbm')->getConfigData('funcional/login');
        $this->_password = Mage::helper('smartpbm')->getConfigData('funcional/password');
        $this->_cnpj = Mage::helper('smartpbm')->getConfigData('funcional/cnpj');
        $this->_crf = Mage::helper('smartpbm')->getConfigData('funcional/crf');
        $this->_ufcrf = Mage::helper('smartpbm')->getConfigData('funcional/ufcrf');
        $this->_tipo = Mage::helper('smartpbm')->getConfigData('funcional/tipo');
        $this->_simulacao = Mage::helper('smartpbm')->getConfigData('funcional/simulacao');
    }

	protected function authentication() {
        if (!$this->_apiKey) {
            $body = [
                'Login'         => $this->_login,
                'Password'      => $this->_password
            ];
            $result = $this->getClient()->post('api/Authentication/UserWebservice',$body);
            if ($result['httpCode'] == 200) {
                $this->_apiKey = $result['body']->ApiKey->Key;
                $this->getClient()->init($this->_url, $this->_apiKey);
            }
        }
    }

    public function consultaProduto($data) {
        $data['produtos'][] = ['id' => $data['product']];
        return $this->consultaProdutos($data);
    }

    public function consultarCartao($cartao) {
        $this->authentication();
        $result = $this->getClient()->get('api/Beneficiario/' . $cartao);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];

            if (isset($resultBody->Errors)) {
                foreach($resultBody->Errors as $error) {
                    $message .= $error->Message . PHP_EOL;
                }
            }

            if (!$resultBody->IsValid) {
                $message = 'Ops! Seu cartão não é válido.';
            }
        }

	    return $message;
    }

    public function consultaProdutos($data) {
        $this->authentication();
        $body = [
            'CNPJ'          => $this->_cnpj,
            'NumeroCartao'  => $data['document'],
            'Itens'         => $this->getEstruturaItens($data)
        ];
        $result = $this->getClient()->post('api/Autorizacao/ConsultarPrecosRegras', $body);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];

            if (isset($resultBody->Errors)) {
                foreach($resultBody->Errors as $error) {
                    $message .= $error->Message . PHP_EOL;
                }
            }

            if ($resultBody->IsValid) {
                return $resultBody;
            }
        }

        return false;
    }

    protected function getEstruturaItens($data) {
        $items = [];

        foreach ($data['produtos'] as $produto) {
            $product = Mage::getModel('catalog/product')->load($produto['id']);
            $discount = Mage::getModel('smartpbm/products')->load($produto['id'], 'product_id');
            //$price = $product->getPrice() * (1 - ($discount->getDiscount()/100));
            $price = $product->getPrice();
            $item = [
                'EAN'               => $discount->getEan(),
                'Preco'             => $price,
                'QuantidadeVenda'   => $data['qty']
            ];

            if (isset($data['beneficio'])) {
                $item['Receita'] = [
                    'Conselho'                  => '0',
                    'SiglaEstado'               => $data['beneficio']['UF'],
                    'NumeroRegistroConselho'    => $data['beneficio']['NumeroRegistroConselho'],
                    'QuantidadeReceita'         => $data['qty'],
                    'DataReceita'               => $this->formatDate($data['beneficio']['receita'])
                ];
            }

            $items[] = $item;
        }

	    return $items;
    }

    protected function getPreAuthItems($collection) {
        $items = [];
        foreach ($collection as $item) {
            $itemData = [
                'EAN'               => $item->getEan(),
                'Preco'             => $item->getFinalprice(),
                'QuantidadeVenda'   => intval($item->getQty())
            ];

            if ($item->getReceipt()) {
                $receipt = json_decode($item->getReceipt());
                $itemData['Receita'] = [
                    'Conselho'                  => '0',
                    'SiglaEstado'               => $receipt->beneficio->UF,
                    'NumeroRegistroConselho'    => $receipt->beneficio->NumeroRegistroConselho,
                    'QuantidadeReceita'         => $receipt->qty,
                    'DataReceita'               => $this->formatDate($receipt->beneficio->receita)
                ];
            }

            $items[] = $itemData;
        }

        return $items;
    }

    protected function formatDate($date) {
        $auxDate = explode('/', $date);
        return $auxDate[2] . '-' . $auxDate[1] . '-' . $auxDate[0] . 'T00:00:00.0000000-03:00';
    }

    public function vendaDireta($data) {
        $this->authentication();
        $receipt = json_decode($data['receipt']);
        $data = array_merge($data, (array)$receipt);
        $data['produtos'][] = ['id' => $data['product_id']];
        $body = [
            'CNPJ'          => $this->_cnpj,
            'NumeroCartao'  => $data['card'],
            'Itens'         => $this->getEstruturaItens($data)
        ];
        $result = $this->getClient()->post('api/Autorizacao/VendaDireta', $body);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];

            if (isset($resultBody->Errors)) {
                foreach($resultBody->Errors as $error) {
                    $message .= $error->Message . PHP_EOL;
                }
            }

            if (!$resultBody->IsValid) {
                $message = 'Ops! A compra desse(s) produto(s) não foi aprovada.';
            }
        }

        return $message;
    }

    public function preAutorizacao($data) {
	    Mage::log('METHOD: preAutorizacao', null, 'funcional.log');
        $this->authentication();
        $defaultData = $data[0];
        $body = [
            'CNPJ'          => $this->_cnpj,
            'NumeroCartao'  => $defaultData->getCard(),
            'Itens'         => $this->getPreAuthItems($data)
        ];
        $result = $this->getClient()->post('api/Autorizacao/PreAutorizar', $body);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];
            if (isset($resultBody->Errors)) {
                foreach($resultBody->Errors as $error) {
                    $message .= $error->Message . PHP_EOL;
                }
            }
            if (!$resultBody->IsValid) {
                $message = 'Ops! A compra desse(s) produto(s) não foi aprovada.';
            } else {
                $transaction = [
                    'NumeroAutorizacao' => $resultBody->Transacao->NumeroAutorizacao,
                    'NumeroSequencial' => $resultBody->Transacao->NumeroSequencial,
                    'DataHora' => $resultBody->Transacao->DataHora,
                ];
                Mage::getResourceModel('smartpbm/order')->saveTransactionInfo($defaultData->getOrderId(), $defaultData->getPbm(), $transaction);
            }
        }

        return $message;
    }

    public function resgataPreAutorizacao($data) {
        Mage::log('METHOD: resgataPreAutorizacao', null, 'funcional.log');
        $this->authentication();
        $defaultData = $data[0];
        $body = [
            'CNPJ'          => $this->_cnpj,
            'NumeroCartao'  => $defaultData['card'],
            'Itens'         => $this->getPreAuthItems($data)
        ];
        $result = $this->getClient()->post('api/Autorizacao/PreAutorizar', $body);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];
            if (isset($resultBody->Errors)) {
                foreach($resultBody->Errors as $error) {
                    $message .= $error->Message . PHP_EOL;
                }
            }
            if (!$resultBody->IsValid) {
                $message = 'Ops! A compra desse(s) produto(s) não foi aprovada.';
            } else {

            }
        }

        return $message;
    }

    public function confirmaPreAutorizacao($data) {
        Mage::log('METHOD: confirmaPreAutorizacao', null, 'funcional.log');
        $this->authentication();
        $defaultData = $data[0];
        $transaction = json_decode($defaultData->getTransactionInfo());
        $body = [
            'CNPJ'                  => $this->_cnpj,
            'NumeroCartao'          => $defaultData->getCard(),
            'NumeroAutorizacao'     => $transaction->NumeroAutorizacao,
            'DataHoraAutorizacao'   => $transaction->DataHora,
            'Itens'                 => $this->getPreAuthItems($data)
        ];
        $result = $this->getClient()->post('api/Autorizacao/VendaPreAutorizada', $body);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];
            if (isset($resultBody->Errors)) {
                foreach($resultBody->Errors as $error) {
                    $message .= $error->Message . PHP_EOL;
                }
            }
            if (!$resultBody->IsValid) {
                $message = 'Ops! A compra desse(s) produto(s) não foi aprovada.';
            } else {
                $transaction->Comprovante = $resultBody->Comprovante;
                Mage::getResourceModel('smartpbm/order')->saveTransactionInfo($defaultData->getOrderId(), $defaultData->getPbm(), $transaction);

                $order = Mage::getModel('sales/order')->load($defaultData->getOrderId());
                $comment = $order->addStatusHistoryComment($transaction->Comprovante);
                $comment->setIsVisibleOnFront(1);
                try {
                    $order->save();
                    $message = 'Compra confirmada com sucesso!';
                } catch(Exception $e) {
                    Mage::log('ERRO COMPROVANTE: ' . $order->getId() . ' = ' . $e->getMessage(), null, 'funcional.log');
                    $message = 'Ops! A compra desse(s) produto(s) não foi aprovada.';
                }
            }
        }

        return $message;
    }

    public function validaBeneficio($data) {
        Mage::log('METHOD: validaBeneficio', null, 'funcional.log');
        $card = $data['document'];
        if (Mage::getResourceModel('smartpbm/products')->checkProgram($data['product'],$this->_name)) {
            $product = Mage::getModel('catalog/product')->load($data['product']);
            if ($product->getCodigoBarras()) {
                $result = $this->consultaProduto($data);
                if ($result) {
                    $sellPrice = str_replace(',','.',$result->Transacao->Itens[0]->PrecoVenda);

                    if (!($quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId())) {
                        $quoteId = $this->initializeQuote();
                    }

                    $quoteData = [
                        'quote_id'          => $quoteId,
                        'pbm'               => 'funcional',
                        'status'            => Onestic_Smartpbm_Model_Resource_Quote::QUOTE_STATUS_PENDING,
                        'card'              => $card,
                        'ean'               => $product->getCodigoBarras(),
                        'product_name'      => $product->getName(),
                        'product_id'        => $product->getId(),
                        'discount'          => $result->Transacao->ValorTotalDesconto,
                        'original_price'    => $product->getPrice(),
                        'finalprice'        => $sellPrice,
                        'qty'               => $data['qty'],
                        'date'              => date('Y-m-d H:i:s'),
                        'receipt'           => json_encode($data),
                        'transaction_info'  => ''
                    ];
                    Mage::getResourceModel('smartpbm/quote')->newQuote($quoteData);

                    return $result->Transacao->ValorTotalDesconto;
                }
            }
        }

        return false;
    }

    protected function initializeQuote() {
	    Mage::getSingleton('checkout/cart')->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        Mage::getSingleton('checkout/session')->getQuote()->save();
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();

	    return $quoteId;
    }

    public function confirmaBeneficio($data) {
        Mage::log('METHOD: confirmaBeneficio', null, 'funcional.log');
        $this->confirmaPreAutorizacao($data);
    }

    public function updateProducts() {
        Mage::log('METHOD: updateProducts', null, 'funcional.log');
        $this->authentication();
        $result = $this->getClient()->get('api/Programa?CNPJ=' . $this->_cnpj);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];
            if (!$resultBody->IsValid) {
                if (isset($resultBody->Errors)) {
                    foreach($resultBody->Errors as $error) {
                        $message .= $error->Message . PHP_EOL;
                    }
                }

                Mage::log('ERROR updateProducts: ' . $message, null, 'funcional.log');

                return false;
            } else {
                $modelProducts = Mage::getModel('smartpbm/products');
                $resource = Mage::getResourceModel('smartpbm/products');
                $resource->truncate();
                foreach ($resultBody->Programas as $program) {
                    foreach ($program->Produtos as $product) {
                        foreach ($product->Eans as $ean) {
                            $discount = $modelProducts->load($ean, 'ean');
                            if (!$discount->getId()) {
                                $realProd = Mage::getModel('catalog/product')->getCollection()
                                                ->addAttributeToSelect('entity_id')
                                                ->addAttributeToFilter('codigo_barras',$ean)
                                                ->getFirstItem();

                                if (!$realProd->getId()) {
                                    continue;
                                }

                                $discount->setProductId($realProd->getId());
                                $discount->setEan($ean);
                                $discount->setPbm('funcional');
                                $discount->setProgram($program->NomePrograma);
                                $discount->setProgramCode($program->CodigoPrograma);
                            }

                            $discount->setDiscount($product->DescontoVendaBase);
                            $discount->setMaxPrice($product->PMC);
                            $discount->setUpdatedAt(date('Y-m-d H:i:s'));
                            try {
                                $discount->save();
                                $modelProducts->clearInstance();
                            } catch (Exception $e) {
                                Mage::log('ERROR updateProduct ' . $ean .': ' . $e->getMessage(), null, 'funcional.log');
                            }
                        }
                    }
                }
            }
        }
    }
}