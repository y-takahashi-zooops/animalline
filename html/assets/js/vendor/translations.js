var Translator = {
    trans: function (key) {
        const translations = {
            "common.select": "選択してください",
            "front.product.out_of_stock_label": "在庫なし",
        };
        return translations[key] || key;
    }
};