<?php
class Onestic_Smartpbm_Block_Register extends Mage_Core_Block_Template {

    protected $entities = [
        1 => [
            'name'  => 'beneficiario',
            'title' => 'Seus dados'
        ],
        2 => [
            'name'  => 'paciente',
            'title' => 'Dados do Paciente'
        ],
        3 => [
            'name'  => 'produto',
            'title' => ''
        ],
        5 => [
            'name'  => 'beneficio',
            'title' => 'Dados da receita'
        ],
    ];

    protected function _construct() {
        parent::_construct();
        $this->setTemplate( 'smartpbm/register_fields.phtml' );
    }

    public function getEntityName() {
        $entity = $this->getFields()[0]->Entidade;

        return $this->entities[$entity]['name'];
    }

    public function getEntityTitle() {
        $entity = $this->getFields()[0]->Entidade;

        return $this->entities[$entity]['title'];
    }

}
