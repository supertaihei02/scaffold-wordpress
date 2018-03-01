document.addEventListener('DOMContentLoaded', function () {
  var
    $span = document.createElement('span'),
    $action = document.getElementById('preview-action'),
    $preview = document.getElementById('post-preview');

  $preview.style.display = "none";
  $span.className = "preview button";
  $span.innerHTML = "プレビュー";
  $action.insertBefore($span, $action.firstChild);

  $span.addEventListener('click', cannotPreview);
  function cannotPreview() {
    alert('先に「下書きとして保存」してください');
  }
}, false);