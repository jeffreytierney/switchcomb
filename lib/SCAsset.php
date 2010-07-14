<?php

require_once 'S3.php';

class SCAsset extends SCBase {
  public $creatorid;
  public $type;
  public $orig_path;
  public $hash;
  public $size;
  public $data;
  public $folder;
  public $md5sum;

  private $param_name;

  private $creatinguser;
  private $acceptable_url_prefixes;

  function __construct($user_id=null, $seed=null) {
    $this->setNull();
    if($seed) {
      $this->creatorid = $user_id;
      if(!$this->creator()->existing) {
        throw new AssetException("You must be logged in to upload an asset", 403);
      }
      if(is_array($seed)) {
        $this->newFromUpload();
      }
      else {
        $this->newFromUrl($seed);
      }
      $this->saveAsset();
    }
    else {
      $this->setNull();
    }

    //var_dump($this->toArray());

  }

  function __destruct() {
    $this->setNull();
  }

  protected function setNull() {
    $this->creatorid = null;
    $this->type = null;
    $this->orig_path = null;
    $this->hash = null;
    $this->size = null;
    $this->data = null;
    $this->md5sum = null;
    $this->folder = "i";
    $this->param_name = "uploadmedia";
    $this->acceptable_url_prefixes = array("http://", "https://", "ftp://");
  }

  private function newFromUpload($info=null) {
    if(!$info) { $info = $_FILES; }

    if($info && isset($info[$this->param_name]) && !$info[$this->param_name]["error"]) {
      $file_info = $info[$this->param_name];
      $this->type = $file_info["type"];
      $this->orig_path = $file_info["name"];
      $this->size = $file_info["size"];
      $this->create_time = time();
      $this->hash = md5($this->orig_path + $this->create_time);
      $this->data = file_get_contents($file_info["tmp_name"]);
      $this->md5sum = base64_encode(md5($this->data, true));
    }

  }

  private function newFromURL($url) {
    $url = $this->checkUrlAcceptable($url);
    $ret = $this->getImageFromUrl($url);

    $file_info = $ret["info"];
    $this->type = $file_info["content_type"];
    $this->orig_path = $file_info["url"];
    $this->size = $file_info["size_download"];
    $this->create_time = time();
    $this->hash = md5($this->orig_path);
    $this->data = $ret["result"];
    $this->md5sum = base64_encode(md5($this->data, true));
  }

  private function checkUrlAcceptable($url) {
    $url = trim($url);
    $acceptable = false;
    foreach ($this->acceptable_url_prefixes as $id => $prefix) {
      if(strpos($url, $prefix) === 0) {
        $acceptable = true;
        break;
      }
    }

    if(!$acceptable) { throw new AssetException(); }

    return $url;
  }

  private function getImageFromUrl($url) {
    $opts = array(
        CURLOPT_URL => $url,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 4
    );

    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    if( ! $result = curl_exec($ch)) {
      $err = curl_error($ch);
      curl_close($ch);
      throw new AssetException($err);
    }
    $ret = array(
      "result" => $result,
      "info" => curl_getinfo( $ch )
    );
    curl_close($ch);
    return $ret;

  }

  private function path() {
    $parts = array($this->folder, $this->hash);
    return implode("/", $parts);
  }

  public function url() {
    $parts = array($this->folder, $this->hash);
    $dev = (SC_ENVIRONMENT == "development") ? "s3.amazonaws.com/" : "";
    return "http://".$dev.SC_IMAGEBUCKET."/".implode("/", $parts);
  }

  public function saveAsset() {
    $meta = array(
      "orig-name" => $this->orig_path
    );
    $s3 = new S3(awsAccessKey, awsSecretKey);
    if ($s3->putObject($this->toArray(), SC_IMAGEBUCKET, $this->path(), S3::ACL_PUBLIC_READ, $meta)) {
      $db = new SCDB();

      $type_array = explode("/", $this->type);

      $db_array = array(
        "asset_user_id"=>$this->creatorid,
        "asset_hash"=>SC::dbString($this->hash),
        "asset_createdate"=>SC::dbDate($this->create_time),
        "asset_type"=>SC::dbString($type_array[0]),
        "asset_mime_type"=>SC::dbString($this->type),
        "asset_orig_path"=>SC::dbString($this->orig_path),
        "asset_size"=>$this->size,
        "asset_folder"=>SC::dbString($this->folder)
      );

      $db->insertFromArray($db_array, "assets");
      //echo $this->url();
    }
  }

  static function assetUrl($hash) {
    $asset = new SCAsset();
    $asset->hash = $hash;
    return $asset->url();
  }

  public function creator() {
    if($this->creatinguser) {
      return $this->creatinguser;
    }
    else if($this->creatorid) {
      $this->creatinguser = new SCUser($this->creatorid);
      return $this->creatinguser;
    }

    return false;
  }

  public function toArray($for_db=false) {
    if(!$for_db) {
      $props = parent::toArray();
      $props["creator"] = $this->creator();
    }

    return $props;
  }

}

class AssetException extends Exception {}

?>
