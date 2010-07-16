<?php

require_once 'EmailParser.php';

class SCEmailParser extends EmailParser {

  public $boardid;
  public $threadid;

  protected function setNull() {
    parent::setNull();
    $this->boardid = null;
    $this->threadid = null;
  }

  protected function parse($email) {
    parent::parse($email);
    $this->getSCFields();
  }


  protected function getSCFields() {
    $thread_re = "/^(?:re\:[\s]*)?\[*([0-9]+)\]/i";
    $board_re = "/([0-9]+)\@/i";
    $board_thread_re = "/([0-9]+).([0-9]+)\@/i";

    /*
    if(preg_match($thread_re, $this->subject, $matches)) {
      $this->threadid = intval($matches[1]);
    }
    */

    if(preg_match($board_re, $this->to_address, $matches)) {
      $this->boardid = intval($matches[1]);
    }
    if(preg_match($board_thread_re, $this->to_address, $matches)) {
      $this->threadid = intval($matches[1]);
      $this->boardid = intval($matches[2]);
    }

    $reply_re = "/on .* wrote:\n(?:\>[^\n]*\n)*>?/i";
    $this->body = trim(preg_replace($reply_re, "", $this->body));

  }


}

class SCEmailParserException extends Exception {}

?>
