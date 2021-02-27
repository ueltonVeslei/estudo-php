<?php
class Biostore_Liquidacaovirtual_Model_Parcelamentotypes
{
	public function toOptionArray()
    {
        return array
        (
			array('value' =>'bragspag', 'label' => 'Braspag'),
			array('value' =>'parcelamento', 'label' => 'Parcelamento Genérico')
		);
	}
}