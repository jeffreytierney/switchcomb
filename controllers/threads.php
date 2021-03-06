<?php

class SCThreadsController {
  public function show() {
    SC::loginRequired();
    global $current_user;
    $api = new SCApi();
    $thread = $api->threads_show();
    //$current_user->setThreadViews($thread->messageid, sizeof($thread->messages())-1);

    $vars = array(
      "thread"=>$thread
    );

    switch ($_GET["__content_type"]) {
      case "json":
        $output = array(
          "content"=>SCPartial::renderToString("thread/thread_messages", $vars)
        );
        echo SC::jsonify($output);
        break;
      case "html":
      default:
        $cs = array(
          "title"=>htmlspecialchars($thread->subject),
          "head"=>SCPartial::renderToString("shared/head"),
          "util_links"=>SCPartial::renderToString("thread/util_links", $vars),
          "content"=>SCPartial::renderToString("thread/thread", $vars)
        );

        SCLayout::render("main", $vars, $cs);
    }
  }
  public function _new() {
    SC::loginRequired();
    global $current_user;

    $type = $_GET["type"] or $type = "text";
    $parent = new SCBoard($_GET["boardid"]);
    $route_params = array("boardid"=>$parent->boardid);

    $vars = array(
      "subject"=>SC::getParam("subject", true),
      "text"=>SC::getParam("text", true),
      "type"=>$type,
      "parent"=>$parent,
      "controller"=>"threads",
      "route_params"=>$route_params,
      "action"=>SCRoutes::set("threads", "create", $route_params)
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
          "title"=>"Create Thread",
          "head"=>SCPartial::renderToString("shared/head"),
          "util_links"=>SCPartial::renderToString("board/newthread_util_links", $vars),
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
        $thread = $api->boards_threads_create();
        $output = array(
          "transfer"=>SCRoutes::set("threads", "show", array("boardid"=>$thread->boardid,"threadid"=>$thread->messageid))
        );
        echo SC::jsonify($output);
        break;
      case "html":
      default:
        try {
          $api = new SCApi();
          $thread = $api->boards_threads_create();
          SC::transfer(SCRoutes::set("threads", "show", array("boardid"=>$thread->boardid,"threadid"=>$thread->messageid)));
        }
        catch(Exception $ex) {
          SC::setFlashMessage($ex->getMessage(), "error");
          $this->_new();
        }
    }
  }
}

$controller = new SCThreadsController();

?>
