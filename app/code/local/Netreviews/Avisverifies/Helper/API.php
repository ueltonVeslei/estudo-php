<?php // Our API CLASS. 
class Netreviews_Avisverifies_Helper_API{ 
    
    protected $request;
    protected $msg = array();
    
    public $currentIp;
    
    public $checksum = array(
        'errorQuery' => 0,
        'errorDiscussion' => 0,
        'insert' => 0,
        'update' => 0,
        'delete' => 0
    ); 
    public $debug = array();

    public function construct(Mage_Core_Controller_Request_Http $request) {
        $this->request = $request;
        if ($request->getPost('message'))
            $this->msg = unserialize($this->AC_decode_base64($request->getPost('message')));
    }
    
    public function msg($index){
        // check for isset is essential, bcz we could have empty $msg.
        return (isset($this->msg[$index])) ? $this->msg[$index] : null ;
    }
    // convert product reviews format
    public function productReviews($websiteId){
        // if null;
        if (!isset($this->msg['data']))
            return array();
        // else
        $reviews = explode("\n",trim($this->msg['data']));// "Array des lignes (séparateur \n)"
        $tmp = array();
        foreach ($reviews as $line) {
            $column = explode("\t",$line); // "Récupération des colonnes pour chaque ligne, dans un array (séparateur \t = tabulation)"
            $data = array();
            switch ($column[0]) {
                case 'NEW': case 'UPDATE':
                    $data = $this->column($column);
                    $data = array_merge($data, $this->discussion($column));
                    break;
                case 'DELETE':
                    $data = array('error' =>false,'query' => $column[0],'id_product_av' => $column[2],'ref_product'=>$column[4]);
                    $this->checksum['delete']++;
                    break;
                case 'AVG':
                    $data = array('query' => $column[0], 'id_product_av' => $column[1],
                    'ref_product' => $column[2],'rate' => $column[3],'error' =>false,
                    'nb_reviews' => urlencode($column[4]),'horodate_update' => time());
                    $this->checksum['update']++;
                    break;
                default:
                    $data = array('id_product_av' => 0,'error' => true);
                    $this->debug[0] = 'Aucune action (NEW, UPDATE, DELETE) envoyée : ['.$column[0].']';
                    $this->checksum['errorQuery']++;
                    break;
            }
            $data = array_merge($data,array('website_id' => $websiteId));
            $tmp[] = $data;
        }
        if (array_sum($this->checksum) != count($reviews)) {
            $this->debug[] = "Une erreur s'est produite. Le nombre de lignes "
                . " reçues ne correspond pas au nombre de lignes traitées par l'API."
                . " Des données ont quand même pu être enregistré";
        }
        return $tmp;
    }
    
    // Diffirent column structure according to diffirent query.
    protected function column($column){
        $customername = "";

        if ( !empty( $column[9] ) && strlen( trim( $column[9] ) ) > 3 ) { // Si le prenom n'est pas vide and have more than 2 characters.
            
            // Clean firstname and lastname
            $column[9] = urlencode( $column[9] );
            $column[9] = str_replace( "%22", "", $column[9] ); // Delete quotation marks.
            $column[9] = urldecode( $column[9] );
            $column[9] = strtolower( $column[9] );
            $column[9] = ucwords( $column[9] );
        
            $customername .=  $column[9];
        } else {
            $customername = "anonymous";
        }
        
        if ( !empty( $column[8] ) && strlen( trim( $column[8] ) ) > 3 && $customername != "anonymous" && $customername != "Anonymous" ) { // Si le nom de famille n'est pas vide and have more than than 2 characters.
            
            // Clean lastname
            $column[8] = urlencode( $column[8] );
            $column[8] = str_replace( "%22", "", $column[8] ); // Delete quotation marks.
            $column[8] = urldecode( $column[8] );
            $column[8] = strtolower( $column[8] );
            $column[8] = ucwords( $column[8] );
    //        $column[8] = strtoupper( $column[8] ); // Uncomment to have all the name in uppercase (french type).
            $lastName = substr( $column[8], 0, 1 );
        
            $customername .= ( ctype_alpha( $lastName ) ) ? " " . $lastName . "." : "";
        } 

        // Clean review text
        $column[6] = urlencode( $column[6] );
        $column[6] = str_replace( "%22", "", $column[6] ); // Delete quotation marks.

        
        return array(
            'query' => $column[0],
            'id_product_av' => $column[2],
            'ref_product' => $column[4],
            'rate' => $column[7],
            'review' => $column[6],
            'order_horodate' => $column[11],
            'horodate' => $column[5],
            'customer_name' => urlencode($customername),
            // $column[9] -> e-mail
            'helpful' => $column[12],
            'helpless' => $column[13],
            'helpfulrating' => ( $column[12] - $column[13] )
            );
        
    }
    
    protected function discussion($column){
        // "Vérification de la présence d'échanges (nombre d'échange stocké dans 11) et chaque échange est de 3."
        // count($column) equal {$column[0]...$column[14]}-> 15 + 3*($column[14]).
        // NB: all this info is councatunated in a string with (séparateur \t = tabulation).
        // empty return true for: '',0,'0',NULL. and that what we are checking for.
        $echange = (!empty($column[14]))? $column[14] : 0;
        // "Teste si le nombre de paramètres est correct : 15 paramètres sont passés puis 3 par échange"
        if (($echange*3 + 15) == count($column)) {
            $discussion = array();
            for ($i = 0 ; $i < $echange ; $i++) {
                
                // Clean reply text
                $column[14+($i*3)+3] = urlencode( $column[14+($i*3)+3] );
                $column[14+($i*3)+3] = str_replace( "%22", "", $column[14+($i*3)+3] ); // Delete quotation marks.
                $column[14+($i*3)+3] = urldecode( $column[14+($i*3)+3] );
                
                $discussion[] =  array(
                    'horodate' => $column[14+($i*3)+1],
                    'origine' => $column[14+($i*3)+2],
                    'commentaire' => $column[14+($i*3)+3],
                );
            }                           
            $this->checksum['insert']++;
            return array('error' =>false,'discussion' => $this->AC_encode_base64(serialize($discussion)));
        }
        else {
            $this->debug[$column[2]] = 'Nombre de paramètres passés par la ligne incohérents (Nb échanges : '.($echange*3).')  : '.(count($column)-15);
            $this->checksum['errorDiscussion']++;
            return array('discussion' => '','error' => true);
        }
    }
    
    
    
    // encode message
    public function AC_encode_base64($sData){ 
        $sBase64 = base64_encode($sData); 
        return strtr($sBase64, '+/', '-_'); 
    } 
    
    
    
    // decode message
    public function AC_decode_base64($sData){ 
        $sBase64 = strtr($sData, '-_', '+/'); 
        return base64_decode($sBase64); 
    }
    
    
    
    // Function to get the client IP address
    public function av_get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if ( $this->avValidateIp( $ip ) ) {
                        $this -> setCurrentIp( $ip );
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    public function avValidateIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }
    
    function getCurrentIp() {
        return $this -> currentIp;
    }

    function setCurrentIp( $currentIp ) {
        $this -> currentIp = $currentIp;
    }


}