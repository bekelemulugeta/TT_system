const hourE1=document.getElementById("hour");
const minuteE1=document.getElementById("minutes");
const secondE1=document.getElementById("seconds");
const ampmE1=document.getElementById("ampm");
const dateE1=document.getElementById("date");

function updateClock(){
    const d = new Date();
    let h=new Date().getHours();
    let m=new Date().getMinutes();
    let s=new Date().getSeconds();
    let ampm="AM";
    if(h>12){
        h=h-12;
        ampm="PM";
    }
h=h<10 ? "0" + h:h;
m=m<10 ? "0" + m:m;
s=s<10 ? "0" + s:s;

    hourE1.innerText="";
    minuteE1.innerText="";
    secondE1.innerText="";
    ampmE1.innerText="";
     dateE1.innerText=d;
  
    setTimeout(() => {updateClock()},1000)


}
updateClock()