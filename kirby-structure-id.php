<?php
/**
 *
 * Structure ID Plugin for Kirby 2
 *
 * @version   1.2.3
 * @author    Sonja Broda <https://www.texniq.de>
 * @copyright Sonja Broda <https://www.texniq.de>
 * @link      https://github.com/texnixe/kirby-structure-id
 * @license   MIT <http://opensource.org/licenses/MIT>
 */


class StructureID {

  protected $hashID;
  protected $structureData;

  public function __construct($structureData, $hashID) {
    $this->structureData = $structureData;
    $this->hashID = $hashID;
  }

  public function getStructureData() {
    return $this->structureData;
  }

  /*
  ** expand the placeholders used in the configuration
  */
  public function expandStructureData() {
    $configData = $this->getStructureData();
    $expandedData = [];
    $expandedData = array_filter ($configData, function($value, $key) use ($configData){
      return strpos($key, '(') === false;
    },ARRAY_FILTER_USE_BOTH);

    $placeholderUris = array_diff_key($configData , $expandedData);

    foreach($placeholderUris as $key => $value) {

      if($key == '(:all)') {
        foreach(kirby()->site()->pages() as $child) {
          $expandedData[$child->uri()] = $value;
        }
      } else if($key == '(:any)') {
        foreach(kirby()->site()->pages() as $child) {
          $expandedData[$child->uri()] = $value;
        }
      } else {

        $pattern = '!(\/)\(:([a-z]{3})\)!';
        $matches = preg_split($pattern, $key, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $uri = $matches[0];
        $placeholder = $matches[2];
        if($p = page($uri)) {
          if($placeholder == 'any') {
            $children = $p->children();
          } elseif ($placeholder == 'all') {
              $children = $p->index();
          }
          foreach($children as $child) {
            $expandedData[$child->uri()] = $value;
          }
        }
      }
    }

    return $expandedData;
  }

  /*
  ** gets the structure field names to update from the configuration
  */
  public function getFields($page) {
    return $this->expandStructureData()[$page->uri()];
  }

  public function getPageKeys() {
    $keys = array_keys($this->expandStructureData());
    return $keys;
  }

  /*
  ** generates a unique hash from time and session ID
  */
  public function generateHash() {
    $hashID = md5(microtime().session_id());

    $callback = c::get('structure.id.hashGenerator');
    if(is_callable($callback)) {
      $hashID = (string) $callback();
    }

    return $hashID;
  }

  /*
  ** returns an array of hashed structure $fields
  */
  public function getHashArray($page, $fields) {

    // set hashID value if the hashID field does not exist or is empty
    $callback = function(&$value, $key, $hashID) {
      if(!isset($value[$hashID]) || isset($value[$hashID]) && $value[$hashID] == '') {
        $value[$hashID] = $this->generateHash();
      }
    };

    foreach($fields as $fieldName) {

      // check if given field exists in the page
      if(in_array($fieldName, array_keys($page->content()->toArray()))) {
        $entries = $page->{$fieldName}()->yaml();
        // make sure we have an array
        if(is_array($entries)) {
          // and add the hashID to each element of the array
          array_walk($entries, $callback, $this->hashID);
        }

        $data = yaml::encode($entries);
        $updateArray[$fieldName] = $data;
      }
    }
    return $updateArray;

  }

  // update page with hashed arrays
  public function updatePage($page) {
    $hashArray = $this->getHashArray($page, $this->getFields($page));
    try {
      $page->update(
        $hashArray
      );
    } catch(Exception $e) {
      error_log($e->getMessage());
    }
  }

}

$hashID = c::get('structure.id.hashfield', 'hash_id');
$structureData = c::get('structure.id.data', []);

// create an instance and hook into kirby
$instance = new StructureID($structureData, $hashID);
// update structure field items with hash on page update

kirby()->hook('panel.page.update', function($page) use ($instance) {
  // get the page keys from the instance
  $pageKeys = $instance->getPageKeys();
  //f::write(kirby()->roots()->index() . '/debug.txt', json_encode($pageKeys));
  $uri = $page->uri();
  // check if the current page uid is in the config array
  if(in_array($uri, $pageKeys)) {
    //f::write(kirby()->roots()->index() . '/debug.txt', 'yes, in keys');
    $instance->updatePage($page);
  }

});
