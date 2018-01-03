<?php
/**
 *
 * Structure ID Plugin for Kirby 2
 *
 * @version   1.0.1
 * @author    Sonja Broda <https://www.texniq.de>
 * @copyright Sonja Broda <https://www.texniq.de>
 * @link      https://github.com/texnixe/kirby-structure-id
 * @license   MIT <http://opensource.org/licenses/MIT>
 */


class StructureID {

  protected $hashID;
  protected $fieldName;


  public function __construct($fieldName, $hashID) {
     $this->fieldName = $fieldName;
     $this->hashID = $hashID;
  }

  public function getFieldName() {
    return $this->fieldName;
  }

  public function generateHash() {

    $elements[] = microtime();
    $elements[] = session_id();
    // Concatenate Elements
    $idString = implode('', $elements);
    // Build Hash
    $hashID = md5($idString);

    return $hashID;
  }


  public function addHashToStructure($page) {

    $callback = function(&$value, $key, $hashID) {
      if(isset($value[$hashID]) && $value[$hashID] == '') {
        $value[$hashID] = $this->generateHash();
      }
    };

    $entries = $page->{$this->fieldName}()->yaml();
    if(is_array($entries)) {
      array_walk($entries, $callback, $this->hashID);
    }

    $data = yaml::encode($entries);

    try {
      $page->update(
        [$this->fieldName => $data]
      );

    } catch(Exception $e) {
      return $e->getMessage();
    }
  }

}

$hashID = c::get('structureid.hashfield.name', 'hash_id');
$fieldName = c::get('structureid.field.name', 'structurefield');

// create an instance and hook into kirby
$instance = new StructureID($fieldName, $hashID);

// Update structure field items with hash on page update
kirby()->hook('panel.page.update', function($page) use ($instance) {
  if($page->{$instance->getFieldName()}()->exists()) {
    return $instance->addHashToStructure($page);
  }
});
