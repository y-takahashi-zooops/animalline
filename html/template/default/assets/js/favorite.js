$("a#favorite").on("click",function() {
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
        }
        else
        {
            $('#textBtnLike').text('お気に入りに追加')
        }
    }
    });
})