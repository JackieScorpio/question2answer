// 自定义的初始化函数
function custom_init() {
    // 如果没有登录，需要监听得到跨域传过来的username
    window.onload = function() {
        window.addEventListener('message', function(event) {
            const currentUsername = event.data;

            // 登录后将current-username在q2a域名端口下也存一份儿，todo：登出的时候清除
            localStorage.setItem('current-username', currentUsername);
            const params = {};
            params.currentUsername = currentUsername;
            
            // qa_ajax_post 要传入第四个参数，否则ajax请求返回的值不对（待研究）
            qa_ajax_post('get_current_username', params,function(res) {
                if (res.sessioncode) {
                    // 构造 qa_session，模拟登录
                    document.cookie = `qa_session=${currentUsername}%2F${res.sessioncode}%2F0`;
			        location.reload();
                }
            }, 1);
        }, false);
    };
}

custom_init();
