/**
 * @author Albert Garipov <bert320@gmail.com>
 */
"use strict";

var Client = function () {
    var object = {

        socket: null,
        onData: null,
        onOpen: null,

        construct: function (socketName) {
            var me = this;
            me.socket = new WebSocket(socketName);
            me.socket.onmessage = function (e) {
                if (me.onData !== null) {
                    me.onData(JSON.parse(e.data));
                }
            };
            me.socket.onopen = function (e) {
                if (me.onOpen !== null) {
                    me.onOpen();
                }
            };
        },

        sendData: function (data) {
            this.socket.send(JSON.stringify(data));
        }

    };
    object.construct.apply(object, arguments);
    return object;
};