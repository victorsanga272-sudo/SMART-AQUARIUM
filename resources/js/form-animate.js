let log_in = document.querySelector('.log-in');
let login_button = document.querySelector('.login-button');

let register_div = document.querySelector('.register');
let register_button = document.querySelector('.register-button');

register_button.addEventListener('click', (e) => {
    e.preventDefault();
    
    // Transform register div to 0deg (visible)
    register_div.style.transform = 'rotateY(-90deg)';
    register_div.style.pointerEvents = 'none';
    
    // Transform login div to 90deg (hidden)
    log_in.style.transform = 'rotateY(0deg)';
    log_in.style.pointerEvents = 'auto';
    document.querySelector('.sign-in-div').style.pointerEvents = 'auto';
});

// Add reverse functionality for login button
login_button.addEventListener('click', (e) => {
    e.preventDefault();
    
    // Transform login div back to 0deg (visible)
    log_in.style.transform = 'rotateY(-90deg)';
    log_in.style.pointerEvents = 'none';
    
    // Transform register div to 90deg (hidden)
    register_div.style.transform = 'rotateY(0deg)';
    register_div.style.pointerEvents = 'auto';
    document.querySelector('.sign-in-div').style.pointerEvents = 'none';
});
//media query  for obile device
let isMobile = window.innerWidth<=768;

if(isMobile){
    document.querySelector('.sign-in-div').style.transform = 'none';
}
register_button.addEventListener('click', (e) => {
    e.preventDefault();
    if(isMobile){
        document.querySelector('.sign-in-div').style.display = 'block';
        document.querySelector('.sign-in-div').style.pointerEvents = 'auto';
        document.querySelector('.log-in-div').style.pointerEvents = 'none';
        document.querySelector('.log-in-div').style.display = 'none';
    }
});
login_button.addEventListener('click', (e) => {
    e.preventDefault();
    if(isMobile){
        document.querySelector('.sign-in-div').style.display = 'none';
        document.querySelector('.sign-in-div').style.pointerEvents = 'none';
        document.querySelector('.log-in-div').style.pointerEvents = 'auto';
        document.querySelector('.log-in-div').style.display = 'block';
    }   
});
