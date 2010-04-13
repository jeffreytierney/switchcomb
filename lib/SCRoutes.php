<?php

class SCRoutes {
  
  static $routeTable = array(
    "index"=>array(
      "index"=>array(
        "route"=>"/",
        "method"=>"GET"
      ),
    ),
    "boards"=>array(
      "_new"=>array(
        "route"=>"/boards/new",
        "method"=>"GET"
      ),
      "create"=>array(
        "route"=>"/boards/create",
        "method"=>"PUT"
      ),
      "unjoined"=>array(
        "route"=>"/boards/unjoined",
        "method"=>"GET"
      ),
      "preview"=>array(
        "route"=>"/boards/:boardid/preview",
        "method"=>"GET"
      ),
      "invitations_new"=>array(
        "route"=>"/boards/:boardid/invitations/new",
        "method"=>"GET"
      ),
      "invitations_create"=>array(
        "route"=>"/boards/:boardid/invitations/create",
        "method"=>"POST"
      ),
      "invitations_redeem"=>array(
        "route"=>"/invitations/:invitecode",
        "method"=>"GET"
      ),
      "show"=>array(
        "route"=>"/boards/:boardid",
        "method"=>"GET"
      )
    ),
    "threads"=>array(
      "_new"=>array(
        "route"=>"/boards/:boardid/threads/new",
        "method"=>"GET"
      ),
      "create"=>array(
        "route"=>"/boards/:boardid/threads/create",
        "method"=>"PUT"
      ),
      "messages_index"=>array(
        "api_route"=>"/boards/:boardid/threads/:threadid",
        "method"=>"GET"
      ),
      "show"=>array(
        "route"=>"/boards/:boardid/threads/:threadid",
        "method"=>"GET"
      )
    ),
    "usersessions"=>array(
      "new"=>array(
        "route"=>"/login",
        "method"=>"GET"
      ),
      "create"=>array(
        "route"=>"/login",
        "api_route"=>"/usersessions/create",
        "method"=>"POST"
      ),
      "delete"=>array(
        "route"=>"/logout",
        "api_route"=>"/usersessions/delete",
        "method"=>"GET,DELETE"
      )
    ),
    "users"=>array(
      "_new"=>array(
        "route"=>"/users/new",
        "method"=>"GET"
      ),
      "create"=>array(
        "route"=>"/users/create",
        "method"=>"PUT"
      ),
      "memberships_index"=>array(
        "route"=>"/users/:userid/memberships",
        "method"=>"GET"
      ),
      "memberships_boardcounts"=>array(
        "route"=>"/users/:userid/memberships/counts",
        "method"=>"GET"
      )
    ),
    "memberships"=>array(
      "show"=>array(
        "route"=>"/users/:userid/memberships/:boardid",
        "method"=>"GET"
      ),
      "delete"=>array(
        "route"=>"/users/:userid/memberships/:boardid",
        "method"=>"DELETE"
      ),
      "create"=>array(
        "route"=>"/users/:userid/memberships/:boardid",
        "method"=>"PUT"
      ),
      "update"=>array(
        "route"=>"/users/:userid/memberships/:boardid",
        "method"=>"POST"
      )
    ),
    "messages"=>array(
      "_new"=>array(
        "route"=>"/boards/:boardid/threads/:threadid/messages/new",
        "method"=>"GET"
      ),
      "create"=>array(
        "route"=>"/boards/:boardid/threads/:threadid/messages/create",
        "method"=>"PUT"
      )
    )

  );
  
  static function checkRequestMethod($method, $required_methods) {
    if(in_array(strtolower($method), explode(",", strtolower($required_methods)))) {
      return true;
    }
    if(strtolower($method)==="post") {
      $posted_method = $_POST["method"];
      if($posted_method && in_array(strtolower($posted_method), explode(",", strtolower($required_methods)))) {
        return true;
      }
    }
    return false;
  }

  static function parseUrl($api=false) {
    $uri = $_SERVER["REQUEST_URI"];
    $method = $_SERVER['REQUEST_METHOD'];
    //echo $uri;
    
    foreach(SCRoutes::$routeTable as $controller=>$actions) {
      foreach($actions as $action=>$params) {
        if(isset($params["route"]) && isset($params["method"])) {
          //if(strcmp(strtolower($params["method"]), strtolower($method)) === 0) {
          if(SCRoutes::checkRequestMethod($method, $params["method"])) {
            if($api && isset($params["api_route"])) {
              $route = SCRoutes::routeToRegex($params["api_route"]);
            }
            else {
              $route = SCRoutes::routeToRegex($params["route"]);
            }
            $match_count = preg_match($route, $uri, $matches);
  
            if($match_count) {
              $params = array();
              foreach($matches as $match_name=>$match_value) {
                if(is_string($match_name)) {
                  if(is_numeric($match_value)) {
                    $match_value = intval($match_value);
                  }
                  $params[$match_name] = $match_value;
                }
              }
              
              $params["controller"] = $controller;
              $params["action"] = $action;
              return $params;
            }
          }
        }
      }
    }
    
  }
  
  static function routeToRegex($route) {
    $url_parts = explode("/", $route);
    for ($i=0;$i<sizeof($url_parts);$i++) {
      if(strpos($url_parts[$i], ":") === 0) {
        $url_parts[$i] = "(?P<".ltrim($url_parts[$i],":").">[^\/\?\#]*)";
      }
    }
    
    $regex =  "/".implode("\/", $url_parts)."([\?\#].*)*$/";
    return $regex;
  }
  
  static function set($controller, $action, $params=false) {
    if(!$params) $params = array();
    if(isset(SCRoutes::$routeTable[$controller]) && isset(SCRoutes::$routeTable[$controller][$action]) && isset(SCRoutes::$routeTable[$controller][$action]["route"])) {
      $route = SCRoutes::$routeTable[$controller][$action]["route"];
      $route_parts = explode("/", $route);
      
      for ($i=0;$i<sizeof($route_parts);$i++) {
        if(strpos($route_parts[$i], ":") === 0) {
          $route_parts[$i] = $params[ltrim($route_parts[$i],":")];
          unset($params[ltrim($route_parts[$i],":")]);
        }
      }
      
      $url = implode("/", $route_parts);
      return str_replace("//", "/", SC::root().$url);
    }
    else {
      return SC::root();
    }
  }
}

class RouteException extends Exception {}

?>
