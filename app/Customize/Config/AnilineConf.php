<?php

namespace Customize\Config;

final class AnilineConf
{
    const ANILINE_ADOPTION_LOGIN_INITIALIZE = 'aniline.login.initialize';
    const ANILINE_ADOPTION_LOGIN_COMPLETE = 'aniline.login.complete';
    const ANILINE_ADOPTION_ENTRY_INDEX_INITIALIZE = 'aniline.adoption.entry.initialize';

    const ANILINE_BREEDER_LOGIN_INITIALIZE = 'aniline.login.initialize';
    const ANILINE_BREEDER_LOGIN_COMPLETE = 'aniline.login.complete';
    const ANILINE_BREEDER_ENTRY_INDEX_INITIALIZE = 'aniline.breeder.entry.initialize';

    //画像の配置場所のベースディレクトリ
    const ANILINE_IMAGE_URL_BASE = 'html/upload/save_image';

    //登録ステータス
    const ANILINE_REGISTER_STATUS_PROVISIONAL = 1;
    const ANILINE_REGISTER_STATUS_ACTIVE = 2;
    const ANILINE_REGISTER_STATUS_EXIT = 3;

    //ペット種別
    const ANILINE_PET_KIND_DOG_CAT = 0;
    const ANILINE_PET_KIND_DOG = 1;
    const ANILINE_PET_KIND_CAT = 2;

    //ペット性別
    const ANILINE_PET_SEX_MALE = 1;
    const ANILINE_PET_SEX_FEMALE = 2;

    const ANILINE_NUMBER_ITEM_PER_PAGE = 12;

    const ROOT_MESSAGE_ID = 0;

    const MESSAGE_FROM_USER = 1;
    const MESSAGE_FROM_MEMBER = 2;

    const CONTACT_TYPE_INQUIRY = 1;
    const CONTACT_TYPE_VISIT_REQUEST = 2;
    const CONTACT_TYPE_REPLY = 3;

    const RESPONSE_UNREPLIED = 0;
    const RESPONSE_REPLIED = 1;

    const CONTRACT_STATUS_UNDER_NEGOTIATION = 0;
    const CONTRACT_STATUS_WAITCONTRACT = 1;
    const CONTRACT_STATUS_CONTRACT = 2;
    const CONTRACT_STATUS_NONCONTRACT = 3;

    const CONTRACT_STATUSES = [
        self::CONTRACT_STATUS_UNDER_NEGOTIATION => '交渉中',
        self::CONTRACT_STATUS_WAITCONTRACT => '成約確認待ち',
        self::CONTRACT_STATUS_CONTRACT => '成約',
        self::CONTRACT_STATUS_NONCONTRACT => '非成約'
    ];
    const CONTRACT_STATUSES_ENABLE = [
        self::CONTRACT_STATUS_CONTRACT,
        self::CONTRACT_STATUS_NONCONTRACT
    ];

    const ADOPTION_VISIBLE_HIDE = 0;
    const ADOPTION_VISIBLE_SHOW = 1;

    const RELEASE_STATUS_PRIVATE = 0;
    const RELEASE_STATUS_PUBLIC = 1;

    const SITE_CATEGORY_BREEDER = 1;
    const SITE_CATEGORY_CONSERVATION = 2;

    const NUMBER_ITEM_TOP = 4;

    const PET_PHOTO_TYPE_IMAGE = 1;
    const PET_PHOTO_TYPE_VIDEO = 2;

    const MOVIE_IN_QUEUE = 0;
    const MOVIE_CONVERT_SUCCESS = 1;
    const MOVIE_CONVERT_FAIL = 2;

    const MOVIE_CONSERVATION_PET = 2;
    const MOVIE_BREEDER_PET = 1;

    const BREEDER_VISIBLE_SHOW = 1;

    const ANILINE_ORGANIZATION_PERSONAL = 0;
    const ANILINE_ORGANIZATION_GROUP = 1;

    const ANILINE_LICENSE_SALES = 1;
    const ANILINE_LICENSE_CUSTODY = 2;
    const ANILINE_LICENSE_LENDING = 3;
    const ANILINE_LICENSE_TRAINING = 4;
    const ANILINE_LICENSE_EXHIBITION = 5;
    const ANILINE_LICENSE_OTHER = 6;

    const PEDIGREE_ORGANIZATION_NONE = 0;
    const PEDIGREE_ORGANIZATION_JKC = 1;
    const PEDIGREE_ORGANIZATION_KC = 2;
    const PEDIGREE_ORGANIZATION_OTHER = 3;

    const EXPERIENCE_NONE = 0;
    const EXPERIENCE_TO_FOUR = 1;
    const EXPERIENCE_TO_NINE = 2;
    const EXPERIENCE_TO_NINETEEN = 3;
    const EXPERIENCE_TO_FORTYNINE = 4;
    const EXPERIENCE_GREATER_FIFTY = 5;

    const NONE = 0;
    const CAN_BE = 1;

    // 審査結果(breedes)
    const ANILINE_EXAMINATION_STATUS_NOT_SUBMIT = 0;    // 審査前
    const ANILINE_EXAMINATION_STATUS_NOT_CHECK = 1;     // 審査中
    const ANILINE_EXAMINATION_STATUS_CHECK_OK = 2;      // 審査OK
    const ANILINE_EXAMINATION_STATUS_CHECK_NG = 3;      // 審査NG

    // 審査結果(breeder_examination_info)
    const ANILINE_EXAMINATION_RESULT_NOT_DECISION = 0;  // 審査決定前
    const ANILINE_EXAMINATION_RESULT_DECISION_OK = 1;   // 審査通過
    const ANILINE_EXAMINATION_RESULT_DECISION_NG = 2;   // 審査拒否

    // 入力状態
    const ANILINE_INPUT_STATUS_INPUT_NOT_COMPLETE = 0;  // 入力未完了
    const ANILINE_INPUT_STATUS_INPUT_COMPLETE = 1;      // 入力完了
    const ANILINE_INPUT_STATUS_SUBMIT = 2;              // 審査中
    const ANILINE_INPUT_STATUS_COMPLETE = 3;            // 完了

    const PUBLIC_FLAG_PRIVATE = 0;
    const PUBLIC_FLAG_RELEASE = 1;

    const DNA_CHECK_RESULT_CHECKING = 0;
    const DNA_CHECK_RESULT_CHECK_OK = 1;
    const DNA_CHECK_RESULT_CHECK_NG = 2;

    const EXERCISE_STATUS_OTHER = 4;

    //犬のサイズ
    const ANILINE_DOG_SIZE_TINNY = 1;
    const ANILINE_DOG_SIZE_SMALL = 2;
    const ANILINE_DOG_SIZE_MEDIUM = 3;
    const ANILINE_DOG_SIZE_BIG = 4;

    // 同期アクション
    const ANILINE_WMS_SYNC_ACTION_PRODUCT = 1; // 商品
    const ANILINE_WMS_SYNC_ACTION_INSTOCK_SCHEDULE = 2; // 入荷予定
    const ANILINE_WMS_SYNC_ACTION_SCHEDULED_SHIPMENT = 4; // 出荷予定
    const ANILINE_WMS_SYNC_ACTION_SCHEDULED_RETURN = 6; // 返品予定

    const ANILINE_WMS_RESULT_SUCCESS = 1;
    const ANILINE_WMS_RESULT_ANNOTATED = 2;
    const ANILINE_WMS_RESULT_ERROR = 3;

    const ANILINE_WMS_WITH_TAX = 1.1;

    const ANILINE_SITE_TYPE_BREEDER = 1; // ブリーダー
    const ANILINE_SITE_TYPE_ADOPTION = 2; // 保護団体

    // ステータス確認
    const ANILINE_DNA_CHECK_STATUS_SHIPPING = 1; // 受付
    const ANILINE_DNA_CHECK_STATUS_PET_REGISTERED = 3; // ペット登録完了
    const ANILINE_DNA_CHECK_STATUS_SPECIMEN_ABNORMALITY = 4; // 検体異常
    const ANILINE_DNA_CHECK_STATUS_CHECKING = 5; // DNA検査中
    const ANILINE_DNA_CHECK_STATUS_PASSED = 6;
    const ANILINE_DNA_CHECK_STATUS_TEST_NG = 7;
    const ANILINE_DNA_CHECK_STATUS_RESENT = 8; // 検体再送付手続済
    const ANILINE_DNA_CHECK_STATUS_PUBLIC = 9;

    const ANILINE_SHIPPING_STATUS_ACCEPT = 1;
    const ANILINE_SHIPPING_STATUS_INSTRUCTING = 2;
    const ANILINE_SHIPPING_STATUS_SHIPPED = 3;

    const ANILINE_RETURN_SCHEDULE = 9;

    const ANILINE_IS_ACTIVE_PUBLIC = 1;
    const ANILINE_IS_ACTIVE_PRIVATE = 0;
}
