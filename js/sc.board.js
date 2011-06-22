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
    this.poll_interval = 20000;
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
                boardname: board.boardname,
                threadid: thread.messageid,
                threadsubject: thread.subject,
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

// the thing that handles the things that show you that there are new messages
SC.Notifier = function(notification_center) {
  this.init(notification_center);
}

SC.Notifier.prototype = {
  constructor: SC.Notifier.prototype.constructor,
  init: function(notification_center) {
    this.notification_center = notification_center;
    this.cacheElements().bindEvents();
  },
  cacheElements: function() {
    return this;
  },
  bindEvents: function() {
    var _this = this;
    SC.CustomEvents.listen("sc_new_messages", function(data) {
      var found_board = false;
      var found_thread = false;
      for (var i=0, len=SC.data.boards.length; i<len; i++) {
        if(SC.data.boards[i].boardid == data.boardid) {
          found_board=true;
        }
      }
      for (var i=0, len=SC.data.threads.length; i<len; i++) {
        if(SC.data.threads[i].threadid == data.threadid) {
          found_thread=true;
        }
        if(SC.data.threads[i].boardid == data.boardid) {
          found_board=true;
        }
      }
      //if(!found_board) {

        if(data.msg_count === 0) {
          var message = "New Thread ("+data.threadsubject+") Created in ";
          message += (found_board ? "this board" : "Board " + data.boardname);

          _this.notification_center.addNotification({
            message: message,
            id: data.boardid + "_"+ data.threadid
          });
        }
        else {
          var new_msg_count = (data.msg_count-data.view_count);
          if(new_msg_count) {
            var message = new_msg_count + " New Message" + ((new_msg_count>1) ? "s" : "") + " in ";
            message += (found_thread ? "this thread" : "thread: " + data.threadsubject);
            if(!found_thread) {
              message += " in " + (found_board ? "this board" : "board " + data.boardname);
            }
            _this.notification_center.addNotification({
              message: message,
              id: data.boardid + "_"+ data.threadid
            });
          }
        }
      //}
    });
    SC.CustomEvents.listen("sc_notification_center_update", function(data) {
      SC.util.setTitleNotifications(data.length);
    });
    return this;
  }
}


SC.NotificationCenter = function(el, badge) {
  this.init(el, badge);
}
SC.NotificationCenter.prototype = {
  constructor: SC.NotificationCenter.prototype.constructor,
  init: function(el, badge) {
    this.el = $(el);
    this.initData();
    this.has_badge = false;
    if(badge) {
      this.has_badge = true;
      this.setupBadge();
    }
    this.bindEvents();
    return this;
  },
  initData: function() {
    this.notifications = [];
    this.notifications_by_id = {};
    return this;
  },
  setupBadge: function() {
    this.badge_el = $(document.createElement("a")).attr("id", "notification_badge").attr("href", "#notifications");;
    this.badge_el_count = $(document.createElement("span")).attr("id", "notification_badge_count");
    this.badge_el.append(this.badge_el_count);
    this.el.prepend(this.badge_el);

    var _this = this;
    SC.CustomEvents.listen("sc_notification_center_update", function(data) {
      _this.badge_el_count.html(data.length);
      if(data.length) {
        _this.badge_el.show();
      }
      else {
        _this.badge_el.hide();
      }
    });

    this.badge_el.bind("click", function() {
      _this.showAllNotifications();
      return false;
    });
  },
  bindEvents: function() {
    var _this = this;
    SC.CustomEvents.listen("sc_remove_notification", function(data) {
      _this.removeNotification(data);
    });
  },
  showAllNotifications: function() {
    for(var i=0, len=this.notifications.length; i<len; i++) {
      this.notifications[i].show();
    }
  },
  addNotification: function(message_obj) {

    if(message_obj.hasOwnProperty("id") && this.notifications_by_id.hasOwnProperty(message_obj.id)) {
      this.notifications[this.notifications_by_id[message_obj.id]].update(message_obj);
    }
    else {
      var id = this.notifications.push(new SC.Notification(message_obj))-1;
      if(message_obj.hasOwnProperty("id")) {
        this.notifications_by_id[message_obj.id] = id;
      }
    }
    this.fireUpdate();
    return this;
  },
  removeNotification: function(id) {
    if(this.notifications_by_id.hasOwnProperty(id)) {
      var idx = this.notifications_by_id[id];
      this.notifications = SC.util.removeArrayItem(this.notifications, idx);
      delete this.notifications_by_id[id];
      // TODO: maybe some cleanup on this to make sure that the notification is destroyed
    }
    this.fireUpdate();
    return this;
  },
  fireUpdate: function() {
    SC.CustomEvents.fire("sc_notification_center_update", this.notifications);
  }
}


// a thing that tells you something
SC.Notification = function(message_obj) {
  this.init(message_obj);
}

SC.Notification.prototype = {
  constructor: SC.Notification.prototype.constructor,
  duration: 10000,
  init: function(message_obj) {
    this.container = $("#notifier");
    this.in_doc = false;
    message_obj.duration = message_obj.hasOwnProperty("duration") ? message_obj.duration : this.duration;
    this.message_obj = message_obj;
    this.display_timer = false;
    this.createEl(message_obj);
    this.bindEvents();
    return this;
  },
  createEl: function(message_obj) {
    this.el = $(document.createElement("div")).addClass("notification");
    if(message_obj.id) {
      this.el.attr("id", message_obj.id);
    }
    this.close = $(document.createElement("a")).addClass("close").html("x").attr("href", "#close");
    this.message = $(document.createElement("p")).addClass("notification_message").html(message_obj.message);
    this.container.append(this.el.append(this.close).append(this.message));
    this.in_doc = true;
    this.appear();
    return this;
  },
  bindEvents: function() {
    var _this = this;
    this.close.bind("click", function() {
      _this.remove();
      _this.in_doc = false;
      return false;
    });
    this.setCloseTimer();

    return this;
  },
  setCloseTimer: function() {
    if(this.message_obj.duration) {
      var _this = this;
      clearTimeout(this.display_timer);
      this.display_timer = setTimeout(function() {
        _this.remove();
      }, this.duration);
    }
  },
  update: function(message_obj) {
    this.message.html(message_obj.message);
    if(!this.in_doc) {
      this.container.append(this.el);
      this.bindEvents();
      this.in_doc = true;
      this.appear();
    }
    else {
      this.el.show();
    }
  },
  appear: function() {
    this.el.hide().fadeIn();
    return this;
  },
  remove: function() {
    var _this = this;
    clearTimeout(this.display_timer);
    this.el.fadeOut(function() {
      var _that = _this;
      $(this).css("visibility", "hidden").show().slideUp(function() {
        $(this).remove().attr("style", "");
        _that.in_doc=false;
      })
    });
    return this;
  },
  show: function() {
    if(!this.in_doc) {
      this.update(this.message_obj);
    }
    this.setCloseTimer();
  }
}



SC.Board = function(el) {
  this.init(el);
}

SC.Board.prototype = {
  constructor: SC.Board.prototype.constructor,
  post_callback_event: "sc_board_newthread",
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
    this.post_callback_event += "_"+this.el.attr("id");
  },
  cacheElements: function() {
    this.els.threadListContainer = this.el.find("#board_threads");
    this.els.threadList = this.els.threadListContainer.find(".boarditem");
    this.els.create_thread_link = $("#create_thread_link");
    this.create_thread_form = new SC.ReplyCreateForm(this.els.create_thread_link.attr("href"), this.post_callback_event);
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

    SC.CustomEvents.listen(this.post_callback_event, function(data) {
      if(data && data.transfer) {
        location.href=data.transfer;
      }
    });

    this.els.create_thread_link.bind("click", function() {
      _this.create_thread_form.show();
      return false;
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
  post_callback_event: "sc_thread_loadnew",
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
    this.post_callback_event += "_"+this.el.attr("id");
    this.reply_form = new SC.ReplyCreateForm(this.el.find(".reply_link").first().attr("href"), this.post_callback_event);
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
      //SC.util.log(JSON.stringify(data));
      var count = data.msg_count - data.view_count;
      if(count) {
        _this.showLoadMore(count);
      }
    });

    SC.CustomEvents.listen(this.post_callback_event, function() {
      _this.loadmore_link.click();
    });

    this.loadmore_link.bind("click", function() {
      _this.loadMessages();
      return false;
    });

    this.els.messageListContainer.bind('click', function(e) {
      if($(e.target).hasClass("reply_link")) {
        _this.reply_form.show();
        return false;
      }
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
    SC.util.addCountToTitle(count);
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
      SC.util.addCountToTitle(0);
      SC.CustomEvents.fire("sc_remove_notification", this.boardid+"_"+this.threadid);
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

SC.ReplyCreateForm = function(url, custom_event) {
  this.init(url, custom_event);
}

SC.ReplyCreateForm.prototype = {
  constructor: SC.ReplyCreateForm.prototype.constructor,
  init: function(url, custom_event) {
    this.load_url = url;
    this.el_id = custom_event;
    this.el = null;
    this.custom_event = custom_event;
    this.loadEl(true);
  },
  cacheElements: function() {
    this.form = this.el.find("form");
    this.text = this.form.find("#message_create_text");
    this.subject = this.form.find("#message_create_subject");
    this.submit = this.form.find("input#btn_create");
    this.create_link_types = this.form.find("#create_link_types");
    this.message_create_type = this.form.find("#message_create_type");
    this.buttons_fieldset = this.form.find(".buttons");
    return this;
  },
  insertElements: function() {
    this.loading = $(document.createElement("span")).addClass("loading").addClass("small").html("Posting...");
    this.cancel = $(document.createElement("input")).attr("type", "button").attr("id", "btn_cancel").val("Cancel").addClass("button");;
    this.json = $(document.createElement("input")).attr("type", "hidden").attr("name", "__content_type").val("json");
    this.form.append(this.loading).append(this.json);
    this.buttons_fieldset.append(this.cancel);
    return this;
  },
  bindEvents: function() {
    var _this = this;
    this.form.bind("submit", function() {
      _this.submit.hide();
      _this.cancel.hide();
      _this.loading.show();
      _this.post();
      //_this.form.find("input, textarea").attr("disabled","true");
      return false;
    });

    this.create_link_types.bind("click", function(e) {
      var target = $(e.target);
      if(target.hasClass("type_link")) {
        var type = target.attr("id").split("_")[1];
        _this.setNewType(type);
        target.closest("ul").find(".active").removeClass("active");
        target.addClass("active");
      }
      return false;
    });

    this.cancel.bind("click", function() {
      _this.hide();
      return false;
    });
    return this;
  },
  loadEl: function(dont_show) {
    var _this = this;
    SC.ajax.GET({
      url: this.load_url,
      success: function(data) {
        _this.createEl(data, dont_show);
      },
      error: SC.ReplyCreateForm.prototype.fallback,
      scope: this
    });
    return this;
  },
  createEl: function(data, dont_show) {
    if(data && data.content) {
      this.el = $(document.createElement("div")).attr("id", this.el_id).addClass("sc_create_reply").addClass("rnd").addClass("bshd");;
      this.el.append(data.content);
      $("body").append(this.el);
      this.cacheElements().insertElements().bindEvents();
      if(!dont_show) {
        this.show();
      }
    }
    return this;
  },
  fallback: function(xhr) {
    alert(xhr.responseText);
    return this;
  },
  show: function() {
    if(this.el) {
      this.el.fadeIn();
    }
    else {
      this.loadEl();
    }
    this.text.focus();
    this.callHook("sc_replyform_show");
    this.el.css({top:$(document.body).scrollTop()+20});
    return this;
  },
  hide: function() {
    var _this = this;
    if(this.el) {
      this.el.fadeOut(function() {
        _this.submit.show();
        _this.cancel.show();
        _this.loading.hide();
        //_this.form.find("input, textarea").attr("disabled",null);
        _this.setNewType();
        _this.form.get(0).reset();
        _this.create_link_types.find(".active").removeClass("active");
        _this.create_link_types.find("li:first a").addClass("active");
      });
    }
    return this;
  },
  post: function() {
    var _this = this;
    var submit_params = {
      success: function(data) {
        _this.postSuccess(data);
      },
      error: function(xhr) {
        _this.postError(xhr);
      },
      dataType:"json"
    };
    this.form.ajaxSubmit(submit_params);
    return this;
  },
  postSuccess: function(data) {
    if(this.custom_event) {
      SC.CustomEvents.fire(this.custom_event, data);
    }
    this.hide();

    return this;
  },
  postError: function(xhr) {
    alert(xhr.reponsetext);
    this.hide();
    return this;
  },
  setNewType: function(type) {
    type = type || "text";
    var message = this.text.val();
    var subject = this.subject.val();

    this.form.attr("class", type);
    this.form.get(0).reset();

    this.text.val(message);
    this.subject.val(subject);
    this.message_create_type.val(type);
    return this;
  }

}

SC.util.acceptsHooks(SC.ReplyCreateForm);

$(function() {
  if(SC.data.current_user) {
    SC.data.boards = [];
    SC.data.threads = [];
    $(".board").each(function(i, val) { SC.data.boards.push(new SC.Board(val));});
    $(".thread").each(function(i, val) { SC.data.threads.push(new SC.Thread(val));});

    SC.data.notifier = new SC.Notifier(new SC.NotificationCenter("#notifier", true));
    SC.data.updater = new SC.Updater();
  }
});
