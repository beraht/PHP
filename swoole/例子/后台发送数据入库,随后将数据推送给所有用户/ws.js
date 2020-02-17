        //websocket  地址
        var  ws_url = "ws://111.229.163.26:8811";
        //创建一个websocket 对象
        var  ws = new WebSocket(ws_url);

        //实例对象的onopen属性
        ws.onopen = function() {
            console.log("client：打开连接");
            ws.send("client：hello，服务端");
        };

        //接收到消息
        ws.onmessage = function(e) {
            console.log("client：接收到服务端的消息 " + e.data);
            // setTimeout(() => {
            //     ws.close();
            // }, 5000);
        };
        //关闭时
        ws.onclose = function(params) {
        console.log("client：关闭连接");
        };
