<?php

class SCMessagesController {
  public function _new() {
    SC::loginRequired();
    global $current_user;

    $vars = array(
      "subject"=>SC::getParam("subject", true),
      "message"=>SC::getParam("message", true),
      "boardid"=>$_GET["boardid"],
      "threadid"=>$_GET["threadid"],
      "thread"=>new SCThread($_GET["threadid"])
    );

    switch ($_GET["__content_type"]) {
      case "json":
        $output = array(
          "content"=>SCPartial::renderToString("message/new", $vars)
        );
        echo SC::jsonify($output);
        break;
      case "html":
      default:

        $cs = array(
          "title"=>"Reply",
          "head"=>SCPartial::renderToString("shared/head"),
          "util_links"=>SCPartial::renderToString("message/util_links", $vars),
          "content"=>SCPartial::renderToString("message/new", $vars)
        );

        SCLayout::render("main", $vars, $cs);
    }
  }
  public function create() {
    SC::loginRequired();
    global $current_user;
    switch ($_GET["__content_type"]) {
      case "json":
        $api = new SCApi();
        $message = $api->threads_messages_create();
        $output = array(
          "query"=>SCRoutes::set("threads", "show", array("boardid"=>$message->boardid,"threadid"=>$message->threadid))
        );
        echo SC::jsonify($output);
        break;
      case "html":
      default:
        try {
          $api = new SCApi();
          $message = $api->threads_messages_create();
          SC::transfer(SCRoutes::set("threads", "show", array("boardid"=>$message->boardid,"threadid"=>$message->threadid)));
        }
        catch(Exception $ex) {
          SC::setFlashMessage($ex->getMessage(), "error");
          $this->_new();
        }
    }
  }
}

$controller = new SCMessagesController();

?>
