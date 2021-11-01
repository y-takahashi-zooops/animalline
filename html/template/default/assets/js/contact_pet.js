function checkContact(petSelf, isSold, isContacted, url) {
    if (petSelf || isContacted || isSold) {
        if (petSelf) {
            alert("自分のペットなので、「この子犬の見学希望／お問い合わせ(無料)」ボタンをクリックしないでください。")
        } else if (isSold) {
            alert("このペットが販売されました。")
        } else if (isContacted) {
            alert("このペットについて、お問い合わせの内容が既に存在しています。")
        }
        window.location.href = url;
    }
}