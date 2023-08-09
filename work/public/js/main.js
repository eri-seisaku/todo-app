'use strict';

{
  // header.html.twigで利用 clickで送信
  const logout = document.querySelector('.logout');
  logout.addEventListener('click', () => {
    if (!confirm('実行しますか?')) {
      return;
    }
    logout.parentNode.submit();
  });

  // index.html.twigで利用 checkで送信
  const checkboxes = document.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', () => {
      checkbox.parentNode.submit();
    });
  });

  // index.html.twigで利用 Todo削除
  // index.html.twigで利用 チェック済みTodo一括削除
  // profile.html.twigで利用 アカウント削除
  const deletes = document.querySelectorAll('.delete');
  deletes.forEach(button => {
    button.addEventListener('click', (e) => {
      if (!confirm('実行しますか?')) {
        e.preventDefault(); // フォーム送信をブロックする
      }
    });
  });

  // index.html.twigで利用 検索機能
  // list.html.twigで利用 検索機能
  // innerTextとtextContentどちらもテキストを取得するためのもの
  const search = document.querySelector('.search');
  search.addEventListener('change', () => {
    let searchValue = document.querySelector(`input[name='search']`).value;
    let todoItems = document.querySelectorAll('.todoItem');
  
    for (let item of todoItems) {
      let todoTitle = item.querySelector('td:nth-child(3)').innerText; // タイトルのテキストを取得
      let todoContent = item.querySelector('td:nth-child(4)').innerText; // コンテンツのテキストを取得
      if (todoTitle.includes(searchValue) || todoContent.includes(searchValue)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    }
  });

  // 表示切り替え 使用しなかった
  // const toggleButtons = document.querySelectorAll('.toggle-button');
  // const toggleContents = document.querySelectorAll('.toggle-content');

  // toggleContents.forEach((content) => {
  //   content.style.display = 'none'; // 最初は非表示にする
  // });

  // toggleButtons.forEach((button, index) => {
  //   button.addEventListener('click', () => {
  //     toggleContents[index].style.display = toggleContents[index].style.display === 'none' ? 'block' : 'none';
  //   });
  // });


}