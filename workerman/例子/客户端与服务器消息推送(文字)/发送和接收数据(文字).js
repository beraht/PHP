var ws =  new WebSocket("ws://127.0.0.1:8282");

//接收到服务器发送的数据
  ws.onmessage = function(e){

   var message =  eval("("+e.data+")");
      switch (message.type){
          case "text":
              $(".chat-content").append(' <div class="chat-text section-left flex"><span class="char-img" style="background-image: url(http://chat.com/static/newcj/img/123.jpg)"></span> <span class="text"><i class="icon icon-sanjiao4 t-32"></i>'+message.data+'</span> </div>');
              return;


      }
}

//点击发送消息
 $(".send-btn").click(function(){

     var text = $(".send-input").val();

     var message = '{"data":"'+text+'","type":"say"}';

     $(".chat-content").append('<div class="chat-text section-right flex"><span class="text"><i class="icon icon-sanjiao3 t-32"></i>'+text+'</span> <span class="char-img" style="background-image: url(http://chat.com/static/newcj/img/132.jpg)"></span> </div>');

     ws.send(message);

     $(".send-input").val("");
 })