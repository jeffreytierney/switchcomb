//TODO: break this out into separate files

SC.Updater = function() {
  this.init();
}
SC.Updater.prototype = {
  constructor: SC.Updater.prototype.constructor,
  init: function() {
    this.membership_url = "/api/users/" + SC.data.current_user.userid + "/memberships/counts";
    this.initData().loadMemberships(true);
    return this;
  },
  initData: function() {
    this.orig_memberships = [];
    this.memberships = {};
    this.updater_index = 0;
    this.poll_interval = 30000;
    return this;
  },
  loadMemberships: function(inital_load) {
    var _this = this;
    SC.ajax.GET({
      url: this.membership_url,
      success: function(data) {
        _this.loadSuccess(data, inital_load);
      },
      error: SC.Updater.prototype.loadError,
      scope: this
    });
    return this;
  },
  loadSuccess: function(data, initial_load) {
    if(data && data.memberships) {
      this.orig_memberships = data.memberships;
      for (var i=0, len=this.orig_memberships.length; i<len; i++) {
        board = this.orig_memberships[i].board;
        if(!this.memberships.hasOwnProperty(board.boardid)) {
          this.memberships[board.boardid] = {};
        }
        if(board.threads) {
          for (var j=0, j_len = board.threads.length; j<j_len; j++) {
            var thread = board.threads[j];
            var view_count = this.orig_memberships[i].view_counts && this.orig_memberships[i].view_counts.view_counts && this.orig_memberships[i].view_counts.view_counts.hasOwnProperty(thread.messageid) ? this.orig_memberships[i].view_counts.view_counts[thread.messageid] : 0;
            var is_new = false;
            if(!this.memberships[board.boardid].hasOwnProperty(thread.messageid)) {
              this.memberships[board.boardid][thread.messageid] = {
                boardid: board.boardid,
                threadid: thread.messageid,
                msg_count: 0,
                view_count: 0,
                orig_msg_count: initial_load ? parseInt(thread.message_count, 10) : 0,
                last_msg_count: initial_load ? parseInt(thread.message_count, 10) : 0
              };
              is_new = true;
            }
            if(is_new || parseInt(thread.message_count, 10) > this.memberships[board.boardid][thread.messageid].msg_count) {
              this.memberships[board.boardid][thread.messageid].last_msg_count = this.memberships[board.boardid][thread.messageid].msg_count
              this.memberships[board.boardid][thread.messageid].msg_count = parseInt(thread.message_count, 10);
              this.memberships[board.boardid][thread.messageid].view_count =  parseInt(view_count, 10);
              if(!initial_load) {
                SC.CustomEvents.fire("sc_new_messages", this.memberships[board.boardid][thread.messageid]);
                SC.CustomEvents.fire("sc_new_messages_board_"+board.boardid, this.memberships[board.boardid][thread.messageid]);
                SC.CustomEvents.fire("sc_new_messages_board_"+board.boardid+"_thread_"+thread.messageid, this.memberships[board.boardid][thread.messageid]);
              }
            }
          }
        }
      }
      //SC.util.log(JSON.stringify(this.memberships));
      this.scheduleUpdater();
    }
    return this;
  },
  loadError: function(xhr) {
    this.scheduleUpdater();
    return this;
  },
  scheduleUpdater: function() {
    var _this = this;
    setTimeout(function() {
      _this.loadMemberships();
    }, this.poll_interval)
    return this;
  }
}

SC.Board = function(el) {
  this.init(el);
}

SC.Board.prototype = {
  constructor: SC.Board.prototype.constructor,
  init: function(el) {
    this.el = $(el);
    this.initData();
    this.cacheElements().dataFromElements().bindEvents();
    var _this = this;
    //setTimeout(function() { _this.pollThreads(); }, this.poll_interval);
    //this.pollThreads();
    return this;
  },
  initData: function() {
    this.els = {};
    this.threads = [];
    this.threads_by_id = {};
    this.newest_thread = 0;
    this.thread_item_prefix = "thread_";
  },
  cacheElements: function() {
    this.els.threadListContainer = this.el.find("#board_threads");
    this.els.threadList = this.els.threadListContainer.find(".boarditem");
    return this;
  },
  dataFromElements: function() {
    //for (var i=0, len=this.els.threadList.length; i<len; i++) {
    //  var thread = $(this.els.threadList.get(i));
    this.boardid = parseInt(this.el.attr("id").replace("board_", ""), 10);
    //alert(this.boardid);
    var _this = this;
    this.els.threadList.each(function(i, thread) {
      thread = $(thread);
      if(!_this.threads_by_id.hasOwnProperty(thread.attr("id"))) {
        var threadid = _this._idFromKey(thread.attr("id"));
        _this.checkSetNewest(threadid);
        var id = _this.threads.push({
          threadid: threadid,
          msg_count_el: thread.find(".board_threadreplies"),
          msg_count: parseInt(thread.find(".board_threadreplies").html(), 10)
        }) - 1;
        _this.threads_by_id[thread.attr("id")] = id;
        //SC.util.log(threadid);
      }
    });
    return this;
  },
  bindEvents: function() {
    var _this = this;
    SC.CustomEvents.listen("sc_new_messages_board_"+this.boardid, function(data) {
      //SC.util.log(JSON.stringify(data));
      if(_this.threads_by_id.hasOwnProperty(_this._keyFromId(data.threadid))) {
        _this.updateMessageCount(data);
      }
      else {
        _this.loadThreads();
      }
    });
    return this;
  },
  checkSetNewest: function(threadid) {
    if(threadid > this.newest_thread) {
      this.newest_thread = threadid;
    }
  },
  updateMessageCount: function(data) {
    var thread = this.threads[this.threads_by_id[this._keyFromId(data.threadid)]];
    if(data.msg_count > thread.msg_count) {
      thread.msg_count = data.msg_count;
      thread.msg_count_el.html(thread.msg_count);
      $("#"+this._keyFromId(data.threadid)).addClass("new");
    }
  },
  loadThreads: function() {
    SC.ajax.GET({
      url: location.href+"?since="+this.newest_thread,
      scope: this,
      success: SC.Board.prototype.loadSuccess,
      error: SC.Board.prototype.loadError
    });
  },
  loadSuccess: function(data) {
    if(data && data.content) {
      this.els.threadListContainer.prepend(data.content);
      this.cacheElements().dataFromElements();
    }
    return this;
  },
  loadError: function() {
    return this;
  },
  _keyFromId: function(id) {
    return this.thread_item_prefix+id;
  },
  _idFromKey: function(key) {
    return parseInt(key.replace(this.thread_item_prefix, ""), 10);
  }
}

SC.Thread = function(el) {
  this.init(el);
}

SC.Thread.prototype = {
  constructor: SC.Thread.prototype.constructor,
  init: function(el) {
    this.el = $(el);
    this.initData();
    this.cacheElements().dataFromElements().bindEvents();
    var _this = this;
    //setTimeout(function() { _this.pollThreads(); }, this.poll_interval);
    //this.pollThreads();
    return this;
  },
  initData: function() {
    this.els = {};
    this.messages = [];
    this.messages_by_id = {};
    this.newest_message = 0;
    this.message_item_prefix = "message_";
  },
  cacheElements: function() {
    this.els.messageListContainer = this.el.find("#messages");
    this.els.messageList = this.els.messageListContainer.find(".message");
    this.loadmore = this.el.find("#loadmore");
    this.loadmore_link = this.loadmore.find("a");
    this.loadmore_count = this.loadmore.find("#loadmore_count");
    return this;
  },
  dataFromElements: function() {
    //for (var i=0, len=this.els.threadList.length; i<len; i++) {
    //  var thread = $(this.els.threadList.get(i));
    var ids = this.el.attr("id").split("_");
    this.boardid = parseInt(ids[1], 10);
    this.threadid = parseInt(ids[2], 10);
    //alert(this.boardid);
    var _this = this;
    this.els.messageList.each(function(i, message) {
      message = $(message);
      if(!_this.messages_by_id.hasOwnProperty(message.attr("id"))) {
        var messageid = _this._idFromKey(message.attr("id"));
        _this.checkSetNewest(messageid);
        var id = _this.messages.push({
          messageid: messageid,
        }) - 1;
        _this.messages_by_id[message.attr("id")] = id;
        //SC.util.log(threadid);
      }
    });
    return this;
  },
  bindEvents: function() {
    var _this = this;
    SC.CustomEvents.listen("sc_new_messages_board_"+this.boardid+"_thread_"+this.threadid, function(data) {
      SC.util.log(JSON.stringify(data));
      var count = data.msg_count - data.view_count;
      _this.showLoadMore(count);
    });
    
    this.loadmore_link.bind("click", function() {
      _this.loadMessages();
      return false;
    });
    return this;
  },
  checkSetNewest: function(messageid) {
    if(messageid > this.newest_message) {
      this.newest_message = messageid;
    }
  },
  showLoadMore: function(count) {
    this.loadmore_count.html(count);
    this.loadmore.show();
  },
  hideLoadMore: function() {
    this.loadmore_count.html(0);
    this.loadmore.hide();
  },
  loadMessages: function() {
    SC.ajax.GET({
      url: this.loadmore_link.attr("href")+"?since="+this.newest_message,
      scope: this,
      success: SC.Thread.prototype.loadSuccess,
      error: SC.Thread.prototype.loadError
    });
  },
  loadSuccess: function(data) {
    if(data && data.content) {
      this.els.messageListContainer.append(data.content);
      this.cacheElements().dataFromElements();
    }
    this.hideLoadMore();
    return this;
  },
  loadError: function() {
    return this;
  },
  _keyFromId: function(id) {
    return this.message_item_prefix+id;
  },
  _idFromKey: function(key) {
    return parseInt(key.replace(this.message_item_prefix, ""), 10);
  }
}

/*
SC.Board = function(el) {
  this.init(el);
}

SC.Board.prototype = {
  constructor: SC.Board.prototype.constructor,
  init: function(el) {
    this.el = $(el);
    this.initData();
    this.cacheElements().dataFromElements().bindEvents();
    var _this = this;
    //setTimeout(function() { _this.pollThreads(); }, this.poll_interval);
    //this.pollThreads();
    return this;
  },
  initData: function() {
    this.els = {};
    this.threads = [];
    this.threads_by_id = {};
    this.newest_thread = 0;
    this.thread_item_prefix = "thread_";
  },
  cacheElements: function() {
    this.els.threadListContainer = this.el.find("#board_threads");
    this.els.threadList = this.els.threadListContainer.find(".boarditem");
    return this;
  },
  dataFromElements: function() {
    //for (var i=0, len=this.els.threadList.length; i<len; i++) {
    //  var thread = $(this.els.threadList.get(i));
    this.boardid = parseInt(this.el.attr("id").replace("board_", ""), 10);
    //alert(this.boardid);
    var _this = this;
    this.els.threadList.each(function(i, thread) {
      thread = $(thread);
      if(!_this.threads_by_id.hasOwnProperty(thread.attr("id"))) {
        var threadid = _this._idFromKey(thread.attr("id"));
        _this.checkSetNewest(threadid);
        var id = _this.threads.push({
          threadid: threadid,
          msg_count_el: thread.find(".board_threadreplies"),
          msg_count: parseInt(thread.find(".board_threadreplies").html(), 10)
        }) - 1;
        _this.threads_by_id[thread.attr("id")] = id;
        //SC.util.log(threadid);
      }
    });
    return this;
  },
  bindEvents: function() {
    var _this = this;
    SC.CustomEvents.listen("sc_new_messages_board_"+this.boardid, function(data) {
      //SC.util.log(JSON.stringify(data));
      if(_this.threads_by_id.hasOwnProperty(_this._keyFromId(data.threadid))) {
        _this.updateMessageCount(data);
      }
      else {
        _this.loadThreads();
      }
    });
    return this;
  },
  checkSetNewest: function(threadid) {
    if(threadid > this.newest_thread) {
      this.newest_thread = threadid;
    }
  },
  updateMessageCount: function(data) {
    var thread = this.threads[this.threads_by_id[this._keyFromId(data.threadid)]];
    if(data.msg_count > thread.msg_count) {
      thread.msg_count = data.msg_count;
      thread.msg_count_el.html(thread.msg_count);
      $("#"+this._keyFromId(data.threadid)).addClass("new");
    }
  },
  loadThreads: function() {
    SC.ajax.GET({
      url: location.href+"?since="+this.newest_thread,
      scope: this,
      success: SC.Board.prototype.loadSuccess,
      error: SC.Board.prototype.loadError
    });
  },
  loadSuccess: function(data) {
    if(data && data.content) {
      this.els.threadListContainer.prepend(data.content);
      this.cacheElements().dataFromElements();
    }
    return this;
  },
  loadError: function() {
    return this;
  },
  _keyFromId: function(id) {
    return this.thread_item_prefix+id;
  },
  _idFromKey: function(key) {
    return parseInt(key.replace(this.thread_item_prefix, ""), 10);
  }
}
*/

$(function() {
  if(SC.data.current_user) {
    $(".board").each(function(i, val) { SC.data.board = new SC.Board(val);});
    $(".thread").each(function(i, val) { SC.data.thread = new SC.Thread(val);});
    SC.data.updater = new SC.Updater();
  }
});
