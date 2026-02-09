// 載入導航欄
(function() {
  // 載入 navbar.php 到頁面中
  fetch('navbar.php')
    .then(response => response.text())
    .then(data => {
      // 將導航欄插入到 body 的最前面
      document.body.insertAdjacentHTML('afterbegin', data);
      
      // 初始化導航欄功能
      initNavbar();
    })
    .catch(error => console.error('載入導航欄失敗:', error));
})();

// 導航欄功能初始化
function initNavbar() {
  const navbarToggle = document.getElementById('navbarToggle');
  const navbarMenu = document.getElementById('navbarMenu');

  if (navbarToggle && navbarMenu) {
    // 漢堡選單切換
    navbarToggle.addEventListener('click', function() {
      navbarToggle.classList.toggle('active');
      navbarMenu.classList.toggle('active');
    });

    // 點擊選單項目後關閉選單
    const navbarLinks = navbarMenu.querySelectorAll('.navbar-link');
    navbarLinks.forEach(link => {
      link.addEventListener('click', function() {
        navbarToggle.classList.remove('active');
        navbarMenu.classList.remove('active');
      });
    });

    // 點擊選單外部關閉選單
    document.addEventListener('click', function(event) {
      const isClickInside = navbarToggle.contains(event.target) || navbarMenu.contains(event.target);
      if (!isClickInside && navbarMenu.classList.contains('active')) {
        navbarToggle.classList.remove('active');
        navbarMenu.classList.remove('active');
      }
    });
  }
}
