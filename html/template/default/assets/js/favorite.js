$("a#favorite").on("click", function () {
    if ($(this).data('login')) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/adoption/pet/detail/favorite_pet',
            data: {
                'id': $(this).data('id')
            },
            success: function (data) {
                if (data == 'liked') {
                    $('#textBtnLike').text('お気に入りから削除')
                } else {
                    $('#textBtnLike').text('お気に入りに追加')
                }
            }
        });
    } else {
        alert('お気に入り機能を利用するにはログインが必要です')
    }
})