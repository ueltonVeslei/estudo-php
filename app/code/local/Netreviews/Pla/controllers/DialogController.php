<?php

require_once(Mage::getModuleDir('controllers', 'Netreviews_Avisverifies') . DS . 'DialogController.php');

class Netreviews_Pla_DialogController extends Netreviews_Avisverifies_DialogController {

    protected function getOrders($DATA, $API) {
        if (property_exists('Netreviews_Avisverifies_DialogController', 'plaModuleCompatibale')) {
            return parent::getOrders($DATA, $API);
        } else {
            return $this->_getOrders_($DATA, $API);
        }
    }

    // FOR OLD MODULE 
    protected function _getOrders_($DATA, $API) {
        $helperData = Mage::helper('avisverifies/Export');
        $helperData->createStoresIds($DATA->allShopIds);
        $helperData->exportStruct($DATA->allowedProducts);

        if ($API->msg('force') == 1) {
            if ($API->msg('date_deb') && $API->msg('date_fin')) {
                $from = date("Y-m-d H:i:s", strtotime($API->msg('date_deb')));
                $to = date("Y-m-d H:i:s", strtotime($API->msg('date_fin')));
                $helperData->createExportAPI(array('flag' => true, 'from' => $from, 'to' => $to));
                $reponse['debug']['mode'] = "[forcé] " . $helperData->count() . " commandes récupérées en force du " . $from . " au " . $to;
            } else { // en cas d'erreur 
                $reponse['debug'][] = "Aucune période renseignée pour la récupération des commandes en mode forcé";
                return $reponse;
            }
        } elseif ($DATA->processChoosen == 'onorder') {
            $helperData->createExportAPI(array('flag' => true));
            $reponse['debug']['mode'] = "[onorder] " . $helperData->count() . " commandes récupérées";
        } elseif ($DATA->processChoosen == 'onorderstatuschange') {
            if (count($DATA->statusChoosen) >= 1) {
                $helperData->createExportAPI(array('flag' => true, 'status' => $DATA->statusChoosen));
                $reponse['debug']['mode'] = "[onorderstatuschange] " . $helperData->count() . " commandes récupérées avec statut " . implode(";", $DATA->statusChoosen);
            } else { // en cas d'erreur
                $reponse['debug'][] = "Aucun statut n'a été renseigné pour la récupération des commandes en fonction de leur statut";
                $reponse['return'] = 2;
                return $reponse;
            }
        } else { // en cas d'erreur
            $reponse['debug'][] = "Aucun évènement onorder ou onorderstatuschange n'a été renseigné pour la récupération des commandes";
            $reponse['return'] = 3;
            return $reponse;
        }
        $ordersIdsMarketPlace = $ordersIds = $tmp = array();
        foreach ($helperData->getDataExport() as $order) {
            $customerEmailExtension = explode('@', $order['email']);
            if (!in_array($customerEmailExtension[1], $DATA->forbiddenMailExtensions)) {
                // save same order info once.
                $id = (int) $order['entity_id'];
                if (empty($tmp[$id])) {
                    $tmp[$id] = array(
                        'id_order'             => $order['order_id'],
                        'date_order'           => $order['timestamp'], //date timestamp de la table orders    
                        'amount_order'         => $order['amount_order'], //date timestamp de la table orders
                        'date_order_formatted' => $order['date'], //date de la table orders formatté			
                        'date_av_getted_order' => $order['date_av_getted_order'], //date de la table order_history de récup par AV
                        'is_flag'              => $order['is_flag'], //si la commande est déjà flaggué		
                        'state_order'          => $order['status_order'], // we use the status and not the state.
                        'firstname_customer'   => $order['prenom'],
                        'lastname_customer'    => $order['nom'],
                        'email_customer'       => $order['email'],
                    ); // add order products as array.
                }
                // if the product exist then do nothing
                if (!$this->productExistInArray($tmp[$id], $order['product_id'])) {
                    // CODE UPDATED 
                    $foo = array(
                        'id_product'   => $order['product_id'],
                        'name_product' => $order['product_name'],
                        'url'          => $order['url'],
                        'url_image'    => $order['url_image'],
                    );
                    for ($i = 1; $i < 11; $i++) {
                        $name = 'info' . $i;
                        // product_mpn
                        if (!empty($order[$name])) {
                            $foo[$name] = $order[$name];
                        }
                    }
                    if (!empty($order['gtin_ean'])) {
                        $foo['GTIN_EAN'] = $order['gtin_ean'];
                    }
                    if (!empty($order['gtin_upc'])) {
                        $foo['GTIN_UPC'] = $order['gtin_upc'];
                    }
                    if (!empty($order['gtin_jan'])) {
                        $foo['GTIN_JAN'] = $order['gtin_jan'];
                    }
                    if (!empty($order['gtin_isbn'])) {
                        $foo['GTIN_ISBN'] = $order['gtin_isbn'];
                    }
                    if (!empty($order['brand'])) {
                        $foo['brand_name'] = $order['brand'];
                    }
                    if (!empty($order['sku'])) {
                        $foo['sku'] = $order['sku'];
                    }
                    if (!empty($order['mpn'])) {
                        $foo['MPN'] = $order['mpn'];
                    }
                    if (!empty($order['category'])) {
                        $foo['category'] = $order['category'];
                    }
                    // CODE UPDATED END HERE
                    $tmp[$id]['products'][] = $foo;
                }
                $ordersIds[] = $id;
            } else {
                $reponse['message']['Emails_Interdits'][] = 'Commande n°' . $order['order_id'] . ' Email:' . $order['email'];
                $id = (int) $order['entity_id'];
                $ordersIdsMarketPlace[] = $id;
            }
        }
        // always change marketplace orders to 1
        $array_chunk = array_chunk($ordersIdsMarketPlace, 50, true);
        foreach ($array_chunk as $array_chunk_ordersIdsMarketPlace) {
            $helperData->updateFlag($array_chunk_ordersIdsMarketPlace);
        }
        // update Flag db;
        $noFlag = $API->msg('no_flag');
        if (isset($noFlag) && $noFlag == 0) {
            $array_chunk = array_chunk($ordersIds, 50, true);
            foreach ($array_chunk as $array_chunk_ordersIds) {
                $helperData->updateFlag($array_chunk_ordersIds);
            }
        }
        // return value

        $reponse['return'] = 1;
        $reponse['query'] = $this->getRequest()->getPost('query'); // get request post
        $reponse['message']['nb_orders'] = count($tmp);
        $reponse['message']['delay'] = $DATA->delay;
        $reponse['message']['nb_orders_bloques'] = 0;
        $reponse['message']['list_orders'] = $tmp;
        $reponse['debug']['force'] = $API->msg('force');
        $reponse['debug']['produit'] = $DATA->allowedProducts;
        $reponse['debug']['no_flag'] = $API->msg('no_flag');
        return $reponse;
    }

}
