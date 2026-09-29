if(localStorage.getItem('authToken')){
    window.location.href='profile.html';
}else{
    window.location.href='login.html';
}