var oinputs = document.getElementsByTagName("input");
window.csrfToken = document.getElementById('csrf-token').value;
let text = document.getElementById('text');
let hill1 = document.getElementById('hill1');
let h = document.getElementById('h');
let hill0 = document.getElementById('hill0');
let hill4 = document.getElementById('hill4');
let hill5 = document.getElementById('hill5');
let star = document.getElementById('star');
let logout = document.getElementById('logout-btn');

window.addEventListener('scroll', () => {
    let value = window.scrollY;
    text.style.wordSpacing = '0rem';
    text.style.marginTop = value *0.65 + 'px';
    text.style.fontSize = (5 + value * 0.01) + 'rem';
    star.style.top = Math.min(value * 0.45,120) + 'px';
    hill0.style.top =-13+ Math.min(value * 0.6,120) + 'px';
    hill5.style.top = -Math.min(value * 0.15 ,33)+ 'px';
    hill5.style.left= Math.min(value * 0.16 ,90)+ 'px';
    h.style.marginTop=-20 +Math.min(value * 0.2,30)+'px';
    hill4.style.top = -Math.min(value * 0.15 ,33)+ 'px';
    hill4.style.left= -Math.min(value * 0.16 ,90)+ 'px';
    hill1.style.opacity=1-Math.min(value * 0.001,0.5);
});


window.onscroll = () => {
            loginForm.classList.remove('active');
        }

        let loginForm = document.querySelector('.login-form');
        let createForm = document.querySelector('.create-form');
        let nameDisplay = document.querySelector('#name');
        let registeredUsers = {};

        document.querySelector('#login-btn').onclick = () => {
            if (loginForm.classList.contains('active')) {
                text.style.wordSpacing = '0rem';
                loginForm.classList.remove('active');
            }else if(createForm.classList.contains('active')){
                createForm.classList.remove('active');
                text.style.wordSpacing = '0rem';
            }else {
                loginForm.classList.add('active');
                text.style.wordSpacing = '48rem';
                document.getElementById("alert0").style.display = "none";
            }
        }
        document.querySelector('#submit-btn').onclick = (event) => {
            let ale=document.getElementById("alert0");
            var time = new Date();
            if ((!oinputs[0].value || !oinputs[1].value)) {
                alert("输入内容不能为空");
            } else {     
                $ajax({
                    method : "post",
                    url : "./files/php/login.php", 
                    data : {
                        username : oinputs[0].value,
                        password : oinputs[1].value                        
                    },
                    success : function(result){
                        var res = JSON.parse(result);
                        if(res.code == 2){      
                            window.csrfToken = res.csrf_token;                      
                            loginForm.classList.remove('active');
                            text.style.wordSpacing = '0rem';
                            ale.className = "alert-success";
                            ale.innerHTML = res.message;
                            window.location.href = 'index.php';
                            alert("登录成功");
                            ale.style.display = "block";
                        }else{
                            ale.className = "alert-warning";
                            ale.innerHTML = res.message;
                            ale.style.display = "block";
                        }
                    },
                    error : function(msg){
                        console.log(msg);
                    }
                });
            }
        }
        if (logout) {
            logout.addEventListener('click', function() {
            let ale=document.getElementById("alert1");
            $ajax({
                method : "post",
                url : "./files/php/logout.php", 
                data:{},
                success : function(result){
                    var res = JSON.parse(result);
                    if(res.code == 2){     
                        window.location.href = 'index.php';                       
                        alert("已退出登录");
                    }else{
                        ale.className = "alert-warning";
                        ale.innerHTML = res.message;
                        ale.style.display = "block";
                    }
                },
                error : function(msg){
                    console.log(msg);
                }
            });

        }   ) }

        
        document.querySelector('#create-btn').onclick = () => {
            loginForm.classList.remove('active');
            createForm.classList.add('active');
        }

        document.querySelector('#register-btn').onclick = () => {
            var time = new Date();
            var ale = document.getElementById("alert");
            if ((!oinputs[5].value || !oinputs[6].value)) {
                alert("输入内容不能为空");
            } else {
                $ajax({
                    method : "post",
                    url : "./files/php/register.php",
                    data : {
                        username : oinputs[5].value,
                        password : oinputs[6].value,
                        create_time : time.getTime()//获取到毫秒数
                    },
                    success : function(result){
                        var res = JSON.parse(result);
                        if(res.code == 3){
                            createForm.classList.remove('active');
                            loginForm.classList.add('active');
                            ale.className = "alert-success";
                            ale.innerHTML = res.message;
                            ale.style.display = "block";
                        }else{
                            ale.className = "alert-warning";
                            ale.innerHTML = res.message;
                            var oinputs = document.getElementsByTagName("input");ale.style.display = "block";
                        }
                    },
                    error : function(msg){
                        console.log(msg);
                    }
                });
            }
        }
