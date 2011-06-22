/**************************
**
**  SC.api.js - Switchcomb client API js interface
**
**
***************************/

/* check for requirements */

if(typeof SC === "undefined") {
  // this SC object can contain any platform / client specific methods / properties necessary
  throw "You must have a base SC platform library included first";
}
if(!SC.hasOwnProperty("ajax")) {
  // however, it must contain an interface for making ajax requests in a property named ajax
  // the details of how the requests get made will likely be platform specific but this library expects
  // the following methods:
  //    SC.ajax.GET
  //    SC.ajax.POST
  //    SC.ajax.PUT
  //    SC.ajax.DELETE
  //  TODO: list expected ajax object params
  throw "The SC library must have an ajax submodule initialized";
}

if(!SC.hasOwnProperty("json")) {
  // it must also contain an interface for parsing / stringifying JSON
  throw "The SC library must have a json submodule initialized";
}
if(SC.hasOwnProperty("api")) {
  // This should probably never get thrown, but just in case something weird happens
  // that causes an api param to get created somewhere else, we dont want to silently
  // overwrite it... we want to throw an error so that it can be investigated
  throw "There is already a SC.api submodule initialized.";
}

// the API singleton
SC.api = (function() {
  // right now everything is prefixed with mobile, but this may change
  var route_prefix = "/api";
  
  // the route table...
  // the key is the method name that will actually get called in the js
  // such as SC.api.ping or SC.api.atoms, etc
  // TODO: add a list of required params for each action
  var routes = {
    CreateBoard: {route: "/boards/create", method: "PUT"},
    UnjoinedBoards: {route: "/boards/unjoined", method: "GET"},
    CreateBoardInvite: {route: "/boards/:boardid/invitations/create", method:"POST"},
    Board: {route: "/boards/:boardid", method: "GET"},
    CreateThread: {route: "/boards/:boardid/threads/create", method: "PUT"},
    Thread: {route: "/boards/:boardid/threads/:threadid", method: "GET"},
    CreateMessage: {route: "/boards/:boardid/threads/:threadid/messages/create", method: "PUT"},
    
    Membership: {route: "/users/:userid/memberships/:boardid", method: "GET"},
    DeleteMembership: {route: "/users/:userid/memberships/:boardid", method: "DELETE"},
    CreateMembership: {route: "/users/:userid/memberships/:boardid", method: "PUT"},
    UpdateMembership: {route: "/users/:userid/memberships/:boardid", method: "POST"},

    Memberships: {route: "/users/:userid/memberships", method: "GET"},
    BoardCounts: {route: "/users/:userid/memberships/counts", method: "GET"},
    CreateUser: {route: "/users/create", method: "PUT"},

    
    Login: {route: "/usersessions/create", method: "POST"},
    Logout: {route: "/usersessions/delete", method: "DELETE"}
    
  }
  
  // this is the method that will process the inputs from the 
  function getRequestObj(action, params, options) {
    // if we dont have this action, throw an error
    if(!routes.hasOwnProperty(action)) {
      throw "This action is not defined in the api routes table";
    }
    // get the entry from the routes table that corresponds to this action
    var action_obj = routes[action];
    
    // and split up the route so that we can replce any url params that we need to replace
    var route_parts = action_obj.route.split("/");
    
    for (var i=0, len=route_parts.length; i<len; i++) {
      // if we have one (one that starts with :)
      if(route_parts[i].indexOf(":") === 0) {
        // get the name of that param
        var key = route_parts[i].slice(1);
        
        // and if we have a params object, and it has a value for this key, replace it
        // (and delete it from the params object so that we can use the remaining params for data)
        if(params && typeof params === "object" && params.hasOwnProperty(key)) {
          route_parts[i] = params[key];
          delete params[key];
        }
        // otherwise if we just have one string, use that to replace
        // (should only be the case where ther e is one 
        else if (params && typeof params === "string") {
          route_parts[i] = params;
        }
        // if we dont have any params, or we have an object, but not a value for this key
        // just delete that param
        // TODO: i will be putting in checks earlier for required params
        else {
          route_parts = route_parts.slice(0,i).concat(route_parts.slice(i+1));
        }
      }
    }
    
    // initialize the return object that will be passed to the ajax request with the url
    var return_obj = {
      url: route_prefix + route_parts.join("/")
    };
    
    // and if we have params, and its an object
    if(params && typeof params === "object") {
      // not anymore
      // if its an array, the api is expecting json, so we unfortunately need to stringify it
      // (may need that also for objects... not sure yet
      //if (params instanceof Array) {
        //params = SC.json.stringify(params);
      //}
      return_obj["data"] = params;
    }
    
    // lastly, if we have an object for options on the ajax request
    // we need to add those in... (this is simple replacement for now... not full extending)
    if(options && typeof options === "object") {
      for (var option in options) {
        return_obj[option] = options[option]; 
      }
    }
    
    return return_obj;
    
  }
  // simple method that makes sure the action is in the routes table and returns the request method necessary
  function getRequestMethod(action) {
    if(!routes.hasOwnProperty(action)) {
      throw "method - This action is not defined in the api routes table";
    }
    // defaults to GET... but it should never default... the value will always be there
    return routes[action].method || "GET";
    
  }
  
  // the meta method... this is what will get called for each api method
  // build params, get the method, and make the call
  function callMethod(action, params, options) {
    var req_obj = getRequestObj(action, params, options);
    var method = getRequestMethod(action);
    return SC.ajax[method](req_obj);
  }
  
  // initialize the object that will be exposed as the api
  var api_obj = {};
  
  // and go through the route table and create an api method for each action
  // calling the callMethod function internally
  for(var action in routes) (function(action) {
    api_obj[action] = function(params, options) {
      return callMethod(action, params, options);
    }
  })(action);
  
  // expose only what we want to expose and be done with it
  return api_obj;

})();

