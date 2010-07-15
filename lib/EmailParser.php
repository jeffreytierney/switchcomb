<?php

class EmailParser {

  public $from;
  public $from_address;
  public $subject;
  public $body;
  public $to;
  public $to_address;
  public $bcc;
  public $attachment;

  public $boardid;
  public $threadid;

  protected $boundary;
  protected $content_type;
  protected $full;
  protected $lines;
  protected $headers;
  protected $messages;
  protected $pointer;
  protected $message_types;

  static $type_priority = array(
    "text/plain",
    "text/html"
  );

  function __construct($email) {
    $this->setNull();
    $this->parse($email);
  }

  function __destruct() {

  }

  protected function setNull() {
    $this->from = null;
    $this->subject = null;
    $this->body = null;
    $this->to = null;
    $this->bcc = null;
    $this->attachment = null;
    $this->content_type = null;
    $this->boundary = null;
    $this->boardid = null;
    $this->threadid = null;
    $this->from_address = null;
    $this->to_address = null;

    $this->full = null;
    $this->headers = array();
    $this->lines = array();
    $this->messages = array();
    $this->message_types = array();
    $this->pointer = 0;
  }

  protected function parse($email) {
    $this->full = trim($email);
    $this->makeArray();
    $this->splitHeaders();
    $this->gatherMessageTypes();
    $this->setBody();

    //echo $this->body;
    //var_dump($this->message_types);
  }

  protected function makeArray() {
    $this->lines = explode("\n", $this->full);
    return $this;
  }

  protected function splitHeaders() {

    $from_re = "/\<([^\>]+)\>/i";

    for($i=0; $i<count($this->lines); $i++) {
      $this->pointer = $i;
      // this is a header
      $this->headers[] = $this->lines[$i]."\n";
      // look out for special headers
      if (preg_match("/^Subject: (.*)/", $this->lines[$i], $matches)) {
          $this->subject = $matches[1];

      }
      if (preg_match("/^From: (.*)/", $this->lines[$i], $matches)) {
          $this->from = $matches[1];
          if(preg_match($from_re, $this->from, $matches)) {
            $this->from_address = $matches[1];
          }
          else {
            $this->from_address = $this->from;
          }
      }
      // TODO: support multiple to addresses, and do the same with cc and bcc
      if (preg_match("/^To: (.*)/", $this->lines[$i], $matches)) {
          $this->to = $matches[1];
          if(preg_match($from_re, $this->to, $matches)) {
            $this->to_address = $matches[1];
          }
          else {
            $this->to_address = $this->to;
          }
      }
      if (preg_match("/^Content-Type: (.*);/", $this->lines[$i], $matches)) {
          $this->type = $matches[1];
      }
      //Content-Type: multipart/alternative; boundary="0-1650587935-1279162082=:54030"
      if (preg_match("/^Content-Type: multipart\/.*; boundary=[\"\']?([^\"\']*)[\"\']?/", $this->lines[$i], $matches)) {
          $this->boundary = $matches[1];
      }

      if (trim($this->lines[$i])==="") {
          // empty line, header section has ended
          break;
      }
    }
    return $this;
  }

  protected function gatherMessageTypes() {
    $this->messages = array_slice($this->lines, $this->pointer);
    if(!$this->boundary) { $this->message_types[$this->type] = trim(implode("\n", $this->messages)); }
    else {
      $split_boundary = "--".$this->boundary."\n";
      $messages = explode($split_boundary, trim(implode("\n", $this->messages)));
      foreach($messages as $id=>$message) {
        $this->checkMessageType($message);
      }
    }
  }

  protected function checkMessageType($message) {
    $message_type = null;
    $attachment_filename = null;
    $body = null;
    $is_multipart = false;

    if (preg_match("/^Content-Type: multipart\/.*; boundary=[\"\']?([^\"\']*)[\"\']?/", $message, $matches)) {
      $is_multipart = true;
      $boundary = $matches[1];
      $message = str_replace("--".$boundary."--\n", "", $message);
      $split_boundary = "--".$boundary."\n";
      $messages = explode($split_boundary, trim($message));
      foreach($messages as $id=>$sub_message) {
        if(strpos($sub_message, $boundary) === false) {
          $this->checkMessageType($sub_message);
        }
      }
    }

    if(!$is_multipart) {
      if (preg_match("/Content-Type: (.*);/", $message, $matches)) {
        $message_type = $matches[1];
      }
      if (preg_match("/Content-Disposition: (?:attachment|inline);[\s]+[\n]?[\s]+filename=[\"\']?([^\"\'\n]*)[\"\']?\n/", $message, $matches)) {
        $attachment_filename = $matches[1];
      }

      $header_end = strpos($message, "\n\n");
      if($header_end !== false) {
        $body = trim(substr($message, $header_end));
      }

      //echo $message_type ."<br/>\n" . $attachment_filename . "<br/>\n" . $body . "<br/><br/>\n";
      if($message_type) {
        $this->message_types[$message_type] = $body;
      }
      if($attachment_filename) {

        $body = base64_decode(str_replace("\n","",$body));

        $this->attachment = array(
          "size"=>strlen($body),
          "type"=>$message_type,
          "name"=>$attachment_filename,
          "body"=>$body,
          "error"=>0
        );
      }
    }
  }

  protected function setBody() {
    $types = SCEmailParser::$type_priority;
    foreach($types as $priority => $type) {
      if(isset($this->message_types[$type])) {
        $this->body = $this->message_types[$type];
        break;
      }
    }

    return $this;
  }


}

class EmailParserException extends Exception {}

?>
