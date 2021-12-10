function checkContact(petSelf, isSold, isContacted, url) {
    if (petSelf || isContacted || isSold) {
        if (petSelf) {
            alert("自分の生体にはお問い合わせできません。")
        } else if (isSold) {
            alert("このペットが販売されました。")
        } else if (isContacted) {
            alert("このペットについて、お問い合わせ中です。マイページからメッセージを送信してください。")
        }
        window.location.href = url;
    }
}