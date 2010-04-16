/****************
sc.js
creates base KM namespace

by default including the following sub namespaces:
:data = > for storing (non-class-specific) data during the lifecycle of a page.
  To avoid polluting the global namespace while creating global data for other objects to access
:lib = > pointer to our currently loaded js library (currently jQuery)
  The intent is to hopefully create an additional layer of abstraction in front of whatever js lib we have to ease any issues in upgrading or migrating
:util = > any utility functions that we create that should be available and callable sitewide
:ajax = > where we create our wrapper around the js library's ajax call... 
  This is used so often throughout the app that it made sense to wrap it and use the wrapper to define our app's defaults.

others are added later in separate files, but these are the base set that are created / used in this file
******************/

// do a quick sanity check to make sure that SC wasnt already created, and if not, create it
if(!SC) var SC = {};

$.extend(SC, { "data":{}, "lib":{}, "util":{}, "ajax":{}});

if(!SC.Config) SC.Config = {};


SC.lib = jQuery;

// util functions

/*************
SC.util.log: output messages to the log (used to smooth over cross browser logging differences, and allow for easy severity setting)
:msg => :string - the message to be logged
:level => :string [free-form] - the level of severity, which is outputted before the message.  default is "INFO"
*************/
SC.util.log = function(msg, level) {

  if(!level) var level = "INFO";

  try{
    if(console) {return console.log(level + ": " + msg);}
    else {return false;}
  }catch(ex) {return false;}
};


SC.util.goHome = function() {
  window.location = "/";
};

/*************
Cookie Monster
*************/

SC.CookieMonster = function(){
  function get(name){
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    
    for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      
      while (c.charAt(0) == ' ') {
        c = c.substring(1,c.length);
      }
      
      if (c.indexOf(nameEQ) == 0) {
        return c.substring(nameEQ.length,c.length);
      }
    }
    return null;
  };

  function set(name, val, days){
    var expires = '';

    if (days) {
      var date = new Date();
      date.setTime(date.getDate() + days);
      var expires = "; expires=" + date.toGMTString();
    } else {
      var expires = "";
    }

    document.cookie = name + "=" + val + expires + "; path=/";
    
    return true;
  };

  function eat(name){
    return set(name, '', -1);
  };

  return {
    get: get,
    set: set,
    eat: eat,
    nom: eat
  };
}();

/*************
SC.util.inherit: provide classical inheritance model of inheritance for classes
:subClass => :class - the class that will receive the methods of the superClass
:superClass => :class - the class whose methods will be inherited
(function borrowed from p. 44 of Pro Javascript Design Patters by Ross Harmes and Dustin Diaz)
*************/
SC.util.inherit = function(subClass, superClass) {
  var F = function() {};
  F.prototype = superClass.prototype;
  subClass.prototype = new F();
  subClass.prototype.constructor = subClass;

  subClass.superclass = superClass.prototype;
  if(superClass.prototype.constructor == Object.prototype.constructor) {
    superClass.prototype.constructor = superClass;
  }
};

/*************
SC.util.functionArgumentsAsArray: pass in the arguments object from another function, and it will return a real array version of them
-- since the arguments object that is created in all functions to expose the passed arguments as a hash is actually an object and not an array
  which is sneaky, since they added a length property, and made the property names of all the arguments the numbers 0 - arguments.length-1
-- this function is useful for when you need to allow a function to be called via .apply(), and you need to potentially accept a variable # of arguments
:args => :object - the arguments object that was native to the function you are calling this function from
*************/
SC.util.functionArgumentsAsArray = function(args) {
  // since the arguments object behaves very much like an array, with a .length property and
  // integers used for property names, we can just call the array slice method on this object
  // and it will return everything as an array.  bwahahaha
  return Array.prototype.slice.call(args);
};



SC.util.queryStringToHash = function(str) {
  if(str.indexOf("?") > -1) {
    str = str.substring(str.indexOf("?")+1);
  }
  else {
    return {};
  }
  
  var pairs = str.split("&");
  var params = {};
  
  var i = pairs.length;
  while (i) {
    var pair = pairs[--i].split("=");
    params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
  }
  
  return params;
}

SC.util.stripQueryString = function(str) {
  if(str.indexOf("?") > -1) {
    str = str.substring(0, str.indexOf("?"));
  }
  
  return str;
}

SC.util.addCountToTitle = function(new_count) {
  var old_title = $("title").html();
  if(new_count) {
    var plus = (old_title.slice(0,2) === "+ ") ? "+ " : "";
    if(plus !== "") {
      old_title = old_title.slice(2);
    }
    var new_title = plus + "(" + new_count + ") " + old_title.replace(/\([0-9\+]+\)/g, "");
  }
  else {
    var new_title = old_title.replace(/\([0-9\+]+\)/g, "");
  }
  $("title").html(new_title);
  return new_title;
}

SC.util.setTitleNotifications = function(has_notifs) {
  var old_title = $("title").html();
  var plus = (old_title.slice(0,2) === "+ ");
  if(has_notifs) {
    if(plus) {
      return;
    }
    else {
      $("title").html("+ "+old_title);
      return;
    }
  }
  if(!has_notifs) {
    if(plus) {
      $("title").html(old_title.slice(2));
      return;
    }
    else {
      return;
    }
  }
}

SC.util.removeArrayItem = function(arr, index_to_remove) {
  return arr.slice(0,index_to_remove).concat(arr.slice(index_to_remove+1));
}
/************************************************************************************************************************************
SC.ajax
************************************************************************************************************************************/
SC.ajax = {
  
  // static methods for creating/returning an instance of the ajax request class created below
  // :obj => :object - see description of object param on SC.ajax.request.prototype._init below
  // including a url property in the object will execute the request immediately... omitting it will prevent that
  // and it can be called later with .execute(url)
  GET:function(obj) {
    return SC.ajax.makeRequest("GET", obj);
  },
  POST:function(obj) {
    return SC.ajax.makeRequest("POST", obj);
  },
  PUT:function(obj) {
    return SC.ajax.makeRequest("PUT", obj);
  },
  DELETE:function(obj) {
    return SC.ajax.makeRequest("DELETE", obj);
  },
  makeRequest: function(type, obj) {
    var request = new SC.ajax.request(obj);
    request.setMethod(type);
    if(request.url) request.execute();
    return request;
  }
};

/*************
SC.ajax.request - the class used to set up an interface for making ajax requests...
  it can be used to make a single request, or can be instantiated and kept around for making multiple requests
:obj => :object - this provides the params for setting up the request, and the default values will be overridden with anything that is passed in here
    defaults:
      data: {},
      timeout: 10000, 
      singleThreaded:false, // set this to true if you want subsequent requests made by this object to cancel any outstanding request.
      dataType: 'json',
      contentType: 'application/json', // if submitting form data via a POST, set this to 'application/x-www-form-urlencoded'
      scope: this, // when you specify a custom success or error function and you want it to be called with a specific object as the scope, pass that in here
      async: true, // make this request asunchronously or not... probably never want to change this, but if you do, do it here
      method: 'GET', // can either pass in one of the other methods, or later call setMethod to change it
      success: function(data) {
        SC.util.log("default success")
      },
      error: function(data) {
        SC.util.log("error making a " + this.type + " request to " + this.url, "ERROR");
      }
*************/
SC.ajax.request = function(obj) {
  this.init(obj);
};

SC.ajax.request.prototype = {
  
  init: function(obj) {
    // if the only thing passed in is a callback function, set that as the success
    // this is not likely, but may happen if you are creating an object for repeat calls and plan to use defaults and specify the url later
    if (SC.lib.isFunction(obj)) this._setSuccess(obj);
    // more likely scenario, where an initialization object is passed in:
    // override the defaults, and then set the success and error functions if passed in, setting the scope
    else {
      SC.lib.extend(this, obj);
      if(obj.success) this._setSuccess(obj.success, this.scope);
      if(obj.error) this._setError(obj.error, this.scope);
    }
    
  },
  /*************
  _setSuccess: function to set the success callback function for this ajax requester
  :success_function => :function - the function to be called... which will get wrapped into a closure, and applied to the defined scope
  :scope => :object/class - object that is to be used as the "this" for the success function that is being called
  *************/
  _setSuccess: function(success_function, scope) {
    if(!scope) scope = this.scope;
    var scope = scope;
    var success_function = success_function;
    
    this.success = function() {
      var args = SC.util.functionArgumentsAsArray( arguments ); 
      success_function.apply(scope, args);
    };
  },
  /*************
  _setError: function to set the error callback function for this ajax requester
  :error_function => :function - the function to be called... which will get wrapped into a closure, and applied to the defined scope
  :scope => :object/class - object that is to be used as the "this" for the error function that is being called
  
  note - if no arguments are passed in, this function will reset the error handler to the default error handler
  *************/  
  _setError: function(error_function, scope) {
    if(typeof error_function !== "undefined") {
      if(!scope) scope = this;
      var scope = scope;
      var error_function = error_function;
      
      this.error = function() {
        var args = SC.util.functionArgumentsAsArray( arguments ); 
        error_function.apply(scope, args);
      };
    }
    else {
      this.error = SC.ajax.request.prototype.error;
    }
  },
  /*************
  setMethod: function to set the requestType of this requester
  :method => :string ["GET","DELETE","PUT","POST] - the method... if the value is not one of the 4 acceptable values, it defaults to get
  *************/  
  setMethod: function(method) {
    switch(method.toLowerCase()) {
      case 'delete':
      case 'put':
      case 'post':
        this.method = method.toUpperCase();
        break;
      default:
        this.method = "GET";
    }
  },

  /*************
  execute: actually do the request
  :url => :uri - path to make request to
  *************/  
  execute: function(url) {
    
    // create a params object to pass to the jQuery ajax call with all the necessary values
    if(url) this.url = url;
    if(this.url) {
      var req = {
        type: this.method,
        url: this.url,
        data: this.data,
        success: this.success,
        dataType: this.dataType,
        contentType: this.contentType,
        error: this.error,
        async: this.async,
        timeout: this.timeout,
        beforeSend: function (XMLHttpRequest) {}
      };
      // and abort if singleThreaded was set, and there is a ending request
      if(this.singleThreaded && this.xhr) this.xhr.abort();

        // do stuff and save the xhr obj
      this.xhr = SC.lib.ajax(req);
      return this;
    }
    else {
      throw("Must have a url set to execute");
    }

  },
  // a way to manually tell this request object to abort the current request
  abort: function() {
    if(this.xhr) this.xhr.abort();
  },
  // defaults... see above for explanation
  data: {},
  timeout: 10000,
  singleThreaded:false,
  dataType: 'json',
  contentType: 'application/json',
  scope: this,
  async: true,
  method: 'GET',
  success: function(data) {
    SC.util.log("default success");
  },
  error: function(data) {
    SC.util.log("error making a " + this.type + " request to " + this.url, "ERROR");
  }
};



/************************************************************************************************************************************
this function will never be called anywhere else... it instantiates itself here, and provides a globally accessible interface to
register handlers for custom events, and also fire them off, triggering all registered handlers
use it by calling:
SC.CustomEvents.listen(eventName, method) // to register a handler to be called when eventName is fired
SC.CustomEvents.fire(eventName, params, scope) // to trigger all registered handlers for  this event, with "params" to be passed into the method to be called
note: params must be a single value (of any type), if you need to pass in multiple values, use an object literal with named properties to set the values,
      and make your handler methods able to handle that
************************************************************************************************************************************/
SC.CustomEvents = new function() {
  this.events = [];

  this.listen = function(eventName, method) {
    if(typeof method == "function") {
      if(!this.events[eventName]) {
        this.events[eventName] = [];
      }
      this.events[eventName].push(method);
    }
  };

  this.fire = function(eventName, params, scope) {
    scope = scope || window;
    if(this.events[eventName]) {
      for (var methodIndex = 0; methodIndex < this.events[eventName].length; methodIndex++) {
        this.events[eventName][methodIndex].call(scope, params);
      }
    }
  };
};


/*************
SC.FlashMessage: message that gets displayed to the user in the designated message area...
:msg => :string - the message to be logged
*************/
$(function() {
  SC.FlashMessage = (function() {
    var messages = [];
    var duration = 3000;
    var showing = false;
    if(!$("#flash_inner p").length) {
      $("#flash_inner").append(document.createElement("p"));
    }
    else {
      messages.push(makeMessageFromString($("#flash_inner p").html(), $("#flash_inner p").attr("class")));
    }
    
    var flash_el = $("#flash");
    var flash_inner_el = $("#flash_inner");
    var flash_text_el = $("#flash_inner p");
    
    function post(msg, immediate) {
      
      if(typeof msg === "string") {
        msg = makeMessageFromString(msg);
      }
      
      if(immediate) {
        messages.unshift(msg);
      }
      else {
        messages.push(msg);
      }
      if(!showing) {
        show()
      }
    }
    
    function error(msg, immediate) {
      if(typeof msg === "string") {
        msg = msg = makeMessageFromString(msg, "error");
      }
      msg.type = "error";
      post(msg, immediate);
      
    }
    
    
    function show() {
      var _show = arguments.callee;
      if(messages.length) {
        var msg = messages.shift();
        showing = true;
        var _flash_el = flash_el;
        var _flash_text_el = flash_text_el;
        var _flash_inner_el = flash_inner_el
        flash_el.show();
        flash_inner_el.attr("class", msg.type);
        flash_text_el.html(msg.msg).attr("class", msg.type);
        
        _flash_inner_el.slideDown(function() {
          setTimeout(function() { 
            _flash_inner_el.slideUp(function() {
              _flash_inner_el.attr("class", "");
              _flash_text_el.html("");
              _flash_text_el.attr("class", "");
              showing = false;
              flash_el.hide();
              _show()
            }); 
          }, msg.dur);
        });
      }
    }
    
    function makeMessageFromString(msg, type) {
      return {
        msg: msg,
        dur: duration,
        type: type || ""
      };
    }
    
    if(messages.length) {
      show();
    }
    
    return {
      post: post,
      error: error
    };
    
  })();
  
});

// set a class to be able to accept hooks... 
SC.util.acceptsHooks = function(which) {
  if(which && typeof which === "function") {
    which.prototype.registerHook = function() {
      var args = Array.prototype.slice.call(arguments);
      return SC.util.registerHook.apply(this, args); 
    }
    which.prototype.callHook = function(hook) {
      var args = Array.prototype.slice.call(arguments);
      SC.util.callHook.apply(this, args);
      return this;
    }
  }
};

// util class for providing the functionality to classes to add in hooks
// for specific actions
SC.util.registerHook = function(which, func, scope) {
  if (typeof this._hooks === "undefined") {
    this._hooks = {};
  }
  if (!this._hooks.hasOwnProperty(which)) {
    this._hooks[which] = [];
  }
  
  if(typeof func === "function") {
    var obj = {
      func: func
    };
    if(scope) {
      obj.scope = scope;
    }
    this._hooks[which].push(obj);
  }
  return this;
};


SC.util.registerGlobalHook = function(which_class, which, func, scope) {
  if (typeof which_class.prototype._global_hooks === "undefined") {
    which_class.prototype._global_hooks = {};
  }
  if (!which_class.prototype._global_hooks.hasOwnProperty(which)) {
    which_class.prototype._global_hooks[which] = [];
  }
  
  if(typeof func === "function") {
    var obj = {
      func: func
    };
    if(scope) {
      obj.scope = scope;
    }
    which_class.prototype._global_hooks[which].push(obj);
  }
  return true;
};


SC.util.callHook = function(which) {
  var args = Array.prototype.slice.call(arguments);
  if(args.length) {
    args.shift();
  }
  if (this._global_hooks && this._global_hooks.hasOwnProperty(which)) {
    for (var i=0, len=this._global_hooks[which].length; i<len; i++) {
      if(this._global_hooks[which][i].func && typeof this._global_hooks[which][i].func === "function") {
        this._global_hooks[which][i].func.apply(this._global_hooks[which][i].scope || this, args);
      }
    }
  }
  
  if (this._hooks && this._hooks.hasOwnProperty(which)) {
    for (var i=0, len=this._hooks[which].length; i<len; i++) {
      if(this._hooks[which][i].func && typeof this._hooks[which][i].func === "function") {
        this._hooks[which][i].func.apply(this._hooks[which][i].scope || this, args);
      }
    }
  }
  return this;
};
