(function() {
  const mainImage = document.getElementById('gallery-main');
  const thumbs = document.querySelectorAll('.gallery-thumb');
  if (!mainImage || thumbs.length === 0) {
    return;
  }

  thumbs.forEach(btn => {
    btn.addEventListener('click', function () {
      const target = this.getAttribute('data-target');
      if (!target) {
        return;
      }
      mainImage.src = target;
      thumbs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });
})();
