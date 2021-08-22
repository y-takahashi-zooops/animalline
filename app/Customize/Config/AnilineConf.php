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
    const ANILINE_PET_KIND_DOG = 1;
    const ANILINE_PET_KIND_CAT = 2;

    //ペット性別
    const ANILINE_PET_SEX_MALE = 1;
    const ANILINE_PET_SEX_FEMALE = 2;

    const ANILINE_NUMBER_ITEM_PER_PAGE = 12;

    const ROOT_MESSAGE_ID = 0;

    const MESSAGE_FROM_USER = 1;
    const MESSAGE_FROM_CONFIGURATION = 2;

    const CONTACT_TYPE_INQUIRY = 1;
    const CONTACT_TYPE_VISIT_REQUEST = 2;
    const CONTACT_TYPE_REPLY = 3;

    const RESPONSE_UNREPLIED = 0;
    const RESPONSE_REPLIED = 1;

    const CONTRACT_STATUS_UNDER_NEGOTIATION = 0;
    const CONTRACT_STATUS_CONTRACT = 1;
    const CONTRACT_STATUS_NONCONTRACT = 2;

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
}
