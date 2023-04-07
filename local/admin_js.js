document.addEventListener('DOMContentLoaded', function(){

let selectUfshop = document.querySelectorAll('.adm-detail-content-cell-r select option');
 selectUfshop.forEach((item) => {
     item.textContent += '(' + item.value  + ')';
 })
});