function checkAndLogin() {
    var username = document.getElementById("exampleInputText").value;
    var password = document.getElementById("exampleInputPassword1").value;
    console.log(username);
    console.log(password);

    if (username == "buaaadmin" && password == "buaaadmin") {
        window.location.href="../../index.html";
    }
    else {
        window.alert("账号或密码错误");
    }
}