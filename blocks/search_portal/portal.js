window.addEventListener('load', function() {
  function toggleActive() {
    this.parentElement.classList.toggle('active');
  }

  var fg = document.getElementsByClassName('filter_group-toggle');
  console.log(fg);
  fg = Array.from(fg);
  fg.forEach(function(el) {
    el.addEventListener('click', toggleActive);
  }); 
})