$(document).on("click", ".btn-like", function () {
    if ($(this).data('login')) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/breeder/pet/detail/favorite_pet',
            data: {
                'id': $(this).data('id')
            },
            success: function (data) {
                if (data == 'liked') {
                    $('#textBtnLike').text('お気に入りから削除')
                    $('svg.heart-icon path').attr('fill', '#FF424F')
                    $('.btn-like').addClass('red-heart')
                } else {
                    $('#textBtnLike').text('お気に入りに追加')
                    $('svg.heart-icon path').attr('fill', 'none')
                    $('.btn-like').removeClass('red-heart')
                }
            }
        });
    } else {
        alert('お気に入り機能を利用するにはログインが必要です')
    }
})