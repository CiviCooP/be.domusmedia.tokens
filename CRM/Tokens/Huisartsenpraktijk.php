<?php

class CRM_Tokens_Huisartsenpraktijk {
	
	private static $singleton;
	
	private $relationship_type_id;
	
	private $location_type_id;
	
	private $praktijk = array();
	
	private function __construct() {
		try {
			$this->relationship_type_id = civicrm_api3('RelationshipType', 'getvalue', array('return' => 'id', 'name_a_b' => 'dokter van'));
		} catch (exception $e) {
			throw new Exception('Could not find relationship type with name: dokter van');
		}
		try {
			$this->location_type_id = civicrm_api3('LocationType', 'getvalue', array('return' => 'id', 'name' => 'praktijkadres'));
		} catch (Exception $e) {
			throw new Exception('Could not find location type with name: praktijkadres');
		}
	}
	
	/**
	 * Singleton function
	 * 
	 * @return CRM_Tokens_Huisartsenpraktijk
	 */
	public static function singleton() {
		if (!self::$singleton) {
			self::$singleton = new CRM_Tokens_Huisartsenpraktijk(); 
		}
		return self::$singleton;
	}
	
  public function tokens(&$tokens) {
    $tokens['contact']['contact.huisartsenpraktijk_naam'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk naam');
		$tokens['contact']['contact.huisartsenpraktijk_adres'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk adres');
		$tokens['contact']['contact.huisartsenpraktijk_postcode'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk postcode');
		$tokens['contact']['contact.huisartsenpraktijk_plaats'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk plaats');

    $tokens['huisartsenpraktijk']['huisartsenpraktijk.naam'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk naam');
    $tokens['huisartsenpraktijk']['huisartsenpraktijk.adres'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk adres');
    $tokens['huisartsenpraktijk']['huisartsenpraktijk.postcode'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk postcode');
    $tokens['huisartsenpraktijk']['huisartsenpraktijk.plaats'] = CRM_Tokens_ExtensionUtil::ts('Huisartsenpraktijk plaats');
  }
	
	
  public function tokenValues(&$values, $cids) {
    $contacts_ids = $cids;
    if (!is_array($cids)) {
      $contacts_ids = array($cids);
    }
    foreach($contacts_ids as $cid) {
      $tokenValue = $this->findPraktijk($cid);
      if (!is_array($cids)) {
        $values['contact.huisartsenpraktijk_naam'] = $tokenValue['naam'];
				$values['contact.huisartsenpraktijk_adres'] = $tokenValue['adres'];
				$values['contact.huisartsenpraktijk_postcode'] = $tokenValue['postcode'];
				$values['contact.huisartsenpraktijk_plaats'] = $tokenValue['plaats'];

        $values['huisartsenpraktijk.naam'] = $tokenValue['naam'];
        $values['huisartsenpraktijk.adres'] = $tokenValue['adres'];
        $values['huisartsenpraktijk.postcode'] = $tokenValue['postcode'];
        $values['huisartsenpraktijk.plaats'] = $tokenValue['plaats'];
      } else {				
				$values[$cid]['contact.huisartsenpraktijk_naam'] = $tokenValue['naam'];
				$values[$cid]['contact.huisartsenpraktijk_adres'] = $tokenValue['adres'];
				$values[$cid]['contact.huisartsenpraktijk_postcode'] = $tokenValue['postcode'];
				$values[$cid]['contact.huisartsenpraktijk_plaats'] = $tokenValue['plaats'];

        $values[$cid]['huisartsenpraktijk.naam'] = $tokenValue['naam'];
        $values[$cid]['huisartsenpraktijk.adres'] = $tokenValue['adres'];
        $values[$cid]['huisartsenpraktijk.postcode'] = $tokenValue['postcode'];
        $values[$cid]['huisartsenpraktijk.plaats'] = $tokenValue['plaats'];
      }
		}
	}
	
	private function findPraktijk($cid) {
		if (isset($this->praktijk[$cid])) {
			return $this->praktijk[$cid];
		}
		$this->praktijk[$cid] = array(
			'naam' => '',
			'adres' => '',
			'postcode' => '',
			'plaats' => '',
		);
		
		try {
			$relationship = civicrm_api3('Relationship', 'getsingle', array(
				'contact_id_a' => $cid,
				'relationship_type_id' => $this->relationship_type_id,
				'is_active' => 1,
			));
			try {
				$this->praktijk[$cid]['naam'] = civicrm_api3('Contact', 'getvalue', array('return' => 'display_name', 'id' => $relationship['contact_id_b']));
			} catch (Exception $e) {
				// Do nothing
			}	
			try {
				$adres = civicrm_api3('Address', 'getsingle', array(
					'contact_id' => $relationship['contact_id_b'],
					'location_type_id' => $this->location_type_id,
				));
				$this->praktijk[$cid]['adres'] = isset($adres['street_address']) ? $adres['street_address'] : '';
				$this->praktijk[$cid]['postcode'] = isset($adres['postal_code']) ? $adres['postal_code'] : '';
				$this->praktijk[$cid]['plaats'] = isset($adres['city']) ? $adres['city'] : '';
			} catch (Exception $e) {
				// Do nothing
			}
		} catch (Exception $e) {
			// Do nothing	
		}		
		return $this->praktijk[$cid];
	}
	
}
