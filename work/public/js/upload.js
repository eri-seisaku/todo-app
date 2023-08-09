'use strict';

{
  const uploadBtn = document.getElementById("uploadBtn");
  const fileInput = document.getElementById("fileInput");
  const form = document.getElementById("fileForm");

  // 1. input[type="file"]）を自動的にクリックする仕組み
  uploadBtn.addEventListener("click", () => {
    fileInput.click();
  });

  // 2. Space 32 / Enter 13 キーでイベント発火
  uploadBtn.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      fileInput.click();
    }
  });

  // keyCodeは非推奨と出るので修正
  // uploadBtn.addEventListener("keydown", (event) => {
  //   if (event.keyCode === 32 || event.keyCode === 13) {
  //     event.preventDefault();
  //     fileInput.click();
  //   }
  // });

  // 3. 確認ダイアログ後に送信
  fileInput.addEventListener("change", () => {
    if (fileInput.files.length > 0) {
      const filename = fileInput.files[0].name;
      const confirmation = confirm(`ファイル名 : ${filename} をアップロードしますか？`);
      if (confirmation) {
        form.submit();
      }
    }
  });


}