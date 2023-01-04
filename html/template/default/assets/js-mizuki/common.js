
$(".mizuki_search-dogs-button_bleeder").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-bleeder_dogs").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".mizuki_search-dogs-button_breed").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-breed_dogs").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".mizuki_search-dogs-button_area").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-area_dogs").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".mizuki_search-dogs-button_area").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-area_dogs").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".mizuki_search-cats-button_bleeder").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-bleeder_cats").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".mizuki_search-cats-button_breed").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-breed_cats").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".mizuki_search-cats-button_area").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-area_cats").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".mizuki_search-cats-button_area").on("click", function () {
  $("*").removeClass("active");
  $("#mizuki_modal-area_cats").addClass("active");
  $("body").css("overflow-y", "hidden");
});

$(".modaal-close").on("click", function () {
  $("*").removeClass("active");
  $("body").css("overflow-y", "auto");
});

$(".mizuki_search-button_confirm-cancel").on("click", function () {
  $("*").removeClass("active");
  $("body").css("overflow-y", "auto");
});

//ブリーダー検索
$("input[id^=breed-area-dog-]").on("change", function () {
  var prefs = [];

  $("input[id^=breed-area-dog-]").each(function(i,elem) {
    if($(elem).prop('checked')) {
      prefs.push($(elem).val());
    }
  });

  var param = {"prefs" : prefs,"pet_kind": 1};
  var json_prefs = JSON.stringify(param);

  $.ajax({
    method: "POST",
    url: "/breeder/ajax/get_breeds_by_pref",
    data: json_prefs,
    contentType: "application/json"
  }).done(function (data, textStatus, jqXHR) {
    var data_stringify = JSON.stringify(data);
    var data_json = JSON.parse(data_stringify);

    var breeds_html = "";
    Object.keys(data_json).forEach(function (key){
      breeds_html += '<li>\n';
      breeds_html += '<input id="breeder-breeds-'+data_json[key].id+'" type="checkbox" name="pet_breed[]" value="'+data_json[key].id+'" />\n';
      breeds_html += '<label class="mizuki_modal-form_breed-checkbox mizuki_modal-form_breed-dogs-checkbox" label for="breeder-breeds-'+data_json[key].id+'">'+data_json[key].breeds_name+'</label>\n';
      breeds_html += '</li>\n';
    });
    //alert(breeds_html);
    $("#breeder_breeds_by_pref").html(breeds_html);
  });
});

//猫
$("input[id^=breed-area-cat-]").on("change", function () {
  var prefs = [];

  $("input[id^=breed-area-cat-]").each(function(i,elem) {
    if($(elem).prop('checked')) {
      prefs.push($(elem).val());
    }
  });

  var param = {"prefs" : prefs,"pet_kind": 2};
  var json_prefs = JSON.stringify(param);

  $.ajax({
    method: "POST",
    url: "/breeder/ajax/get_breeds_by_pref",
    data: json_prefs,
    contentType: "application/json"
  }).done(function (data, textStatus, jqXHR) {
    var data_stringify = JSON.stringify(data);
    var data_json = JSON.parse(data_stringify);

    var breeds_html = "";
    Object.keys(data_json).forEach(function (key){
      breeds_html += '<li>\n';
      breeds_html += '<input id="breeder-breeds-cat'+data_json[key].id+'" type="checkbox" name="pet_breed[]" value="'+data_json[key].id+'" />\n';
      breeds_html += '<label class="mizuki_modal-form_breed-checkbox mizuki_modal-form_breed-cats-checkbox" label for="breeder-breeds-cat'+data_json[key].id+'">'+data_json[key].breeds_name+'</label>\n';
      breeds_html += '</li>\n';
    });
    //alert(breeds_html);
    $("#breeder_breeds_by_pref_cat").html(breeds_html);
  });
});


//犬種から探す
//サイズ
$("input[id^=dog_size-]").on("click", function () {
  var size = [];

  $("input[id^=dog_size-]").each(function(i,elem) {
    if($(elem).prop('checked')) {
      size.push($(elem).val());
    }
  });

  var param = {"size" : size};
  var json_size = JSON.stringify(param);

  $.ajax({
    method: "POST",
    url: "/breeder/ajax/get_breeds_by_size",
    data: json_size,
    contentType: "application/json"
  }).done(function (data, textStatus, jqXHR) {
    var data_stringify = JSON.stringify(data);
    var data_json = JSON.parse(data_stringify);

    var breeds_html = "";
    Object.keys(data_json).forEach(function (key){
      breeds_html += '<li>\n';
      breeds_html += '<input id="dog-breeds-'+data_json[key].id+'" type="checkbox" name="pet_breed[]" value="'+data_json[key].id+'" />\n';
      breeds_html += '<label class="mizuki_modal-form_breed-checkbox mizuki_modal-form_breed-dogs-checkbox" label for="dog-breeds-'+data_json[key].id+'">'+data_json[key].breeds_name+'</label>\n';
      breeds_html += '</li>\n';

      $("#breeder_breeds_by_size").html(breeds_html);

      //犬種から都道府県
      $(document).on("click", "#dog-breeds-"+data_json[key].id, function () {
        //alert("GO");

        var breeds = [];

        $("input[id^=dog-breeds-]").each(function(i,elem) {
          if($(elem).prop('checked')) {
            breeds.push($(elem).val());
          }
        });

        var param = {"breeds" : breeds,"pet_kind": 1};
        var json_size = JSON.stringify(param);

        $.ajax({
          method: "POST",
          url: "/breeder/ajax/get_pref_by_breeds",
          data: json_size,
          contentType: "application/json"
        }).done(function (data, textStatus, jqXHR) {
          var data_stringify = JSON.stringify(data);
          var data_json = JSON.parse(data_stringify);

          var breeds_html = "";
          Object.keys(data_json).forEach(function (key){
            breeds_html += '<li>\n';
            breeds_html += '<input id="dog_breed_area-'+data_json[key].id+'" value="'+data_json[key].id+'" name="pet_breed_area[]" type="checkbox"/>\n';
            breeds_html += '<label class="mizuki_modal-form_area-checkbox mizuki_modal-form_area-dogs-checkbox" for="dog_breed_area-'+data_json[key].id+'">'+data_json[key].name+'</label>\n';
            breeds_html += '</li>\n';
          });
          //alert(breeds_html);
          $("#dog_pref_by_breeds").html(breeds_html);
        });
      });
    });
    //alert(breeds_html);
    //$("#breeder_breeds_by_size").html(breeds_html);
  });
});

//ブリーダー全都道府県
$("#breed-area-check-all").on("click", function () {
  //alert(this.checked);

  $("input[id^=breed-area-dog-]").prop("checked",this.checked).trigger("change");
});

//わんちゃん全犬種
$("#dog-breed-check-all").on("click", function () {
  //alert(this.checked);

  $("input[id^=dog-breeds-]").prop("checked",this.checked).trigger("change");
});

//わんちゃん全都道府県
$("#dog-area-check-all").on("click", function () {
  //alert(this.checked);

  $("input[id^=dog-area-]").prop("checked",this.checked).trigger("change");
});

//ねこちゃん全都道府県
$("#cat-area-check-all").on("click", function () {
  //alert(this.checked);

  $("input[id^=cat-area-]").prop("checked",this.checked).trigger("change");
});

//猫ブリーダー全都道府県
$("#cat-breed-area-check-all").on("click", function () {
  //alert(this.checked);

  $("input[id^=breed-area-cat-]").prop("checked",this.checked).trigger("change");
});

//ねこちゃん全犬種
$("#cat-breed-check-all").on("click", function () {
  //alert(this.checked);

  $("input[id^=cat-breeds-]").prop("checked",this.checked).trigger("change");
});

//犬種から都道府県
$("input[id^=dog-breeds-]").on("change", function () {
  //alert("GO");

  var breeds = [];

  $("input[id^=dog-breeds-]").each(function(i,elem) {
    if($(elem).prop('checked')) {
      breeds.push($(elem).val());
    }
  });

  var param = {"breeds" : breeds,"pet_kind": 1};
  var json_size = JSON.stringify(param);

  $.ajax({
    method: "POST",
    url: "/breeder/ajax/get_pref_by_breeds",
    data: json_size,
    contentType: "application/json"
  }).done(function (data, textStatus, jqXHR) {
    var data_stringify = JSON.stringify(data);
    var data_json = JSON.parse(data_stringify);

    var breeds_html = "";
    Object.keys(data_json).forEach(function (key){
      breeds_html += '<li>\n';
      breeds_html += '<input id="dog_breed_area-'+data_json[key].id+'" value="'+data_json[key].id+'" name="pet_breed_area[]" type="checkbox"/>\n';
      breeds_html += '<label class="mizuki_modal-form_area-checkbox mizuki_modal-form_area-dogs-checkbox" for="dog_breed_area-'+data_json[key].id+'">'+data_json[key].name+'</label>\n';
      breeds_html += '</li>\n';
    });
    //alert(breeds_html);
    $("#dog_pref_by_breeds").html(breeds_html);
  });
});

//猫種から都道府県
$("input[id^=cat-breeds-]").on("change", function () {
  //alert("GO");

  var breeds = [];
  $("input[id^=cat-breeds-]").each(function(i,elem) {
    if($(elem).prop('checked')) {
      breeds.push($(elem).val());
    }
  });

  var param = {"breeds" : breeds,"pet_kind": 2};
  var json_size = JSON.stringify(param);

  $.ajax({
    method: "POST",
    url: "/breeder/ajax/get_pref_by_breeds",
    data: json_size,
    contentType: "application/json"
  }).done(function (data, textStatus, jqXHR) {
    var data_stringify = JSON.stringify(data);
    var data_json = JSON.parse(data_stringify);

    var breeds_html = "";
    Object.keys(data_json).forEach(function (key){
      breeds_html += '<li>\n';
      breeds_html += '<input id="dog_breed_area-'+data_json[key].id+'" value="'+data_json[key].id+'" name="pet_breed_area[]" type="checkbox"/>\n';
      breeds_html += '<label class="mizuki_modal-form_area-checkbox mizuki_modal-form_area-dogs-checkbox" for="dog_breed_area-'+data_json[key].id+'">'+data_json[key].name+'</label>\n';
      breeds_html += '</li>\n';
    });
    //alert(breeds_html);
    $("#cat_pref_by_breeds").html(breeds_html);
  });
});

//地域から探す
//犬種検索
$("input[id^=dog-area-]").on("change", function () {
  var prefs = [];

  $("input[id^=dog-area-]").each(function(i,elem) {
    if($(elem).prop('checked')) {
      prefs.push($(elem).val());
    }
  });

  var param = {"prefs" : prefs,"pet_kind": 1};
  var json_prefs = JSON.stringify(param);

  $.ajax({
    method: "POST",
    url: "/breeder/ajax/get_breeds_by_pref",
    data: json_prefs,
    contentType: "application/json"
  }).done(function (data, textStatus, jqXHR) {
    var data_stringify = JSON.stringify(data);
    var data_json = JSON.parse(data_stringify);

    var breeds_html = "";
    Object.keys(data_json).forEach(function (key){
      breeds_html += '<li>\n';
      breeds_html += '<input id="area-dog-breeds-'+data_json[key].id+'" type="checkbox" name="pet_breed[]" value="'+data_json[key].id+'" />\n';
      breeds_html += '<label class="mizuki_modal-form_breed-checkbox mizuki_modal-form_breed-dogs-checkbox" label for="area-dog-breeds-'+data_json[key].id+'">'+data_json[key].breeds_name+'</label>\n';
      breeds_html += '</li>\n';
    });
    //alert(breeds_html);
    $("#dog_breeds_by_pref").html(breeds_html);
  });
});

//猫種検索
$("input[id^=cat-area-]").on("change", function () {
  var prefs = [];

  $("input[id^=cat-area-]").each(function(i,elem) {
    if($(elem).prop('checked')) {
      prefs.push($(elem).val());
    }
  });

  var param = {"prefs" : prefs,"pet_kind": 2};
  var json_prefs = JSON.stringify(param);

  $.ajax({
    method: "POST",
    url: "/breeder/ajax/get_breeds_by_pref",
    data: json_prefs,
    contentType: "application/json"
  }).done(function (data, textStatus, jqXHR) {
    var data_stringify = JSON.stringify(data);
    var data_json = JSON.parse(data_stringify);

    var breeds_html = "";
    Object.keys(data_json).forEach(function (key){
      breeds_html += '<li>\n';
      breeds_html += '<input id="area-cat-breeds-'+data_json[key].id+'" type="checkbox" name="pet_breed[]" value="'+data_json[key].id+'" />\n';
      breeds_html += '<label class="mizuki_modal-form_breed-checkbox mizuki_modal-form_breed-cats-checkbox" label for="area-cat-breeds-'+data_json[key].id+'">'+data_json[key].breeds_name+'</label>\n';
      breeds_html += '</li>\n';
    });
    //alert(breeds_html);
    $("#cat_breeds_by_pref").html(breeds_html);
  });
});

// 全選択/解除

$(function () {
  var checkAll = "#checkAll"; //「すべて」のチェックボックスのidを指定
  var checkBox = 'input[name="dog_breed-area"]'; //チェックボックスのnameを指定

  $(checkAll).on("click", function () {
    $(checkBox).prop("checked", $(this).is(":checked"));
  });

  $(checkBox).on("click", function () {
    var boxCount = $(checkBox).length; //全チェックボックスの数を取得
    var checked = $(checkBox + ":checked").length; //チェックされているチェックボックスの数を取得
    if (checked === boxCount) {
      $(checkAll).prop("checked", true);
    } else {
      $(checkAll).prop("checked", false);
    }
  });
});

// メリットのアコーディオン

$(".mizuki_advantage-down-arrow").on("click", function () {
  var findElm = $(this).prev(".mizuki_advantage-detail");
  $(findElm).slideToggle();
  $(this).addClass("open");

  if ($(this).hasClass("open")) {
    $(this).next(".mizuki_advantage-top-arrow").addClass("open");
  }
});

$(".mizuki_advantage-top-arrow").on("click", function () {
  var findElm = $(this).prev().prev(".mizuki_advantage-detail");
  $(findElm).slideToggle();
  $(this).removeClass("open");

  if ($(this).hasClass("open")) {
    $(this).prev(".mizuki_advantage-down-arrow").removeClass("open");
  } else {
    $(this).prev(".mizuki_advantage-down-arrow").removeClass("open");
  }
});

// DNA検査のアコーディオン

jQuery(function () {
  jQuery(".mizuki_dna-test_faq-question").click(function () {
    jQuery(this).next().slideToggle(200);
    jQuery(this).toggleClass("active", 200);
  });
});

// 新着ペットタブ切り替え

$(".mizuki_pet-info-tab_dog").on("click", function () {
  $(".mizuki_pet-list_dog").addClass("active");
  $(".mizuki_pet-info-tab").addClass("active");
  $(".mizuki_pet-list_cat").removeClass("active");
});

$(".mizuki_pet-info-tab_cat").on("click", function () {
  $(".mizuki_pet-list_cat").addClass("active");
  $(".mizuki_pet-info-tab").removeClass("active");
  $(".mizuki_pet-list_dog").removeClass("active");
});
