<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'v1'], function ($router) {

    $router->post('register', 'AuthController@register');
    $router->post('checkMail', 'AuthController@checkMail');
    $router->post('lostMail', 'UserController@mailForPasswordLost');
    $router->post('lostPassword', 'UserController@passwordLost');
    $router->post('checkPassword', 'AuthController@checkPassword');
    $router->post('login', 'AuthController@login');
    $router->post('me', [
        'middleware' => 'role:10',
        'uses' =>   'AuthController@me'
    ]);
    $router->post('logout', [
        'middleware' => 'role:10',
        'uses' =>   'AuthController@logout'
    ]);
    $router->post('extendtoken', [
        'middleware' => 'role:10',
        'uses' =>   'AuthController@extendToken'
    ]);
    //Change User Group
    $router->put('/usergroup', [
        'middleware' => 'role:80',
        'uses' =>   'UserController@changerUserGroup'
    ]);
    //USER
    $router->get('/users', [
        'middleware' => 'role:80',
        'uses' =>   'UserController@getUsersList'
    ]);
    $router->get('/proUsers', [
        'middleware' => 'role:10',
        'uses' =>   'UserController@getProUsersList'
    ]);
    $router->get('users/{id}', [
        'middleware' => 'role:80',
        'uses' =>    'UserController@getOneUser'
    ]);
    $router->group(['prefix' => 'user'], function ($router) {
        
        $router->get('/', [
            'middleware' => 'role:10',
            'uses' =>    'UserController@getUserDescription'
        ]);
        $router->post('/', [
            'middleware' => 'role:10',
            'uses' =>   'UserController@addDescription'
        ]);
        $router->put('/', [
            'middleware' => 'role:10',
            'uses' =>    'UserController@updateDescription'
        ]);
        $router->delete('/', [
            'middleware' => 'role:10',
            'uses' =>    'UserController@deleteUser'
        ]);
        $router->put('/password', [
            'middleware' => 'role:10',
            'uses' =>    'UserController@passwordModify'
        ]);
    });
    //USER PHOTO
    $router->post('/userphoto', [
        'middleware' => 'role:10',
        'uses' =>    'UserController@addUserPhoto'
    ]);
    $router->put('/userphoto', [
        'middleware' => 'role:10',
        'uses' => 'UserController@updateUserPhoto'
    ]);
    $router->delete('/userphoto', [
        'middleware' => 'role:10',
        'uses' => 'UserController@deleteUserPhoto'
    ]);
    $router->get('user/{fileName}', 'UserController@showUserPhoto');
    $router->post('city', 'ContentController@searchCity');
    // ARTICLE
    $router->get('/articles', [
        'middleware' => 'role:10',
        'uses' =>    'ContentController@getAllBlogArticlesNoLimit'
    ]);
    $router->get('/articles/{id}', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@getBlogArticleForBuild'
    ]);
    $router->get('favoriteArticle', 'ContentController@searchBlogArticleByLike');
    $router->get('myArticle', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@getAllBlogArticlesByUser'
    ]);
    $router->get('/articleAdmin', [
        'middleware' => 'role:80',
        'uses' =>    'ContentController@getAllBlogArticlesForAdmin'
    ]);
    $router->group(['prefix' => 'article'], function ($router) {
        $router->get('/{id}', [
            'middleware' => 'role:10',
            'uses' =>    'ContentController@getBlogArticle'
        ]);
        $router->get('/', [
            'middleware' => 'role:10',
            'uses' =>    'ContentController@getAllBlogArticles'
        ]);
        $router->post('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@postBlogArticle'
        ]);
        $router->put('/{id}', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@updateBlogArticle'
        ]);
        $router->delete('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@deleteBlogArticle'
        ]);
    });
    $router->get('/getPublicBlogArticle/{id}', 'ContentController@getPublicBlogArticle');
    
    //PARAGRAPHE
    $router->group(['prefix' => 'text'], function ($router) {
        $router->post('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@addText'
        ]);
        $router->put('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@updateText'
        ]);
        $router->delete('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@deleteParagraph'
        ]);
        $router->get('/{id}', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@getText'
        ]);
    });
    $router->post('/text-photo', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@addParagraphPhoto'
    ]);
    $router->delete('/text-photo', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@deleteParagraphPhoto'
    ]);
    //PHOTOS D'ARTICLES
    $router->group(['prefix' => 'photo'], function ($router) {
        $router->post('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@addPhoto'
        ]);
        $router->put('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@updatePhoto'
        ]);
        $router->delete('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@deletePhoto'
        ]);
    });
    $router->get('photo/{fileName}', 'ContentController@showPhoto');
    //VIDEOS D'ARTICLES
    $router->group(['prefix' => 'video'], function ($router) {
        $router->post('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@addVideo'
        ]);
        $router->put('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@updateVideo'
        ]);
        $router->delete('/', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@deleteVideo'
        ]);
        
    });
    $router->get('videoAccueil/{id}', 'ContentController@getVideoByIdForAccueil');
    
    $router->get('videos/{id}', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@getVideoById'
    ]);
    $router->get('allvideo', 'ContentController@getAllVideo');
    $router->get('video/{fileName}', 'ContentController@showVideo');
    $router->get('/videos', 'ContentController@getVideoCategory');
    $router->post('/videos-cat', 'ContentController@getVideoByCat');
    $router->post('/rechercheVideo', 'ContentController@searchLdsVideo');
    $router->post('/videoContent', 'ContentController@getLdsVideoByIdContent');
    $router->get('/likeVideos', 'ContentController@searchLdsVideoByLike');
    $router->post('/likeVideosCat', 'ContentController@searchLdsVideoByCatName');
    $router->get('/countVideos', 'ContentController@countVideosLDSF');
    $router->get('/countVideosAccueil', 'ContentController@countVideosForAccueil');
    $router->get('/countMyVideos', 'ContentController@countMyVideosLDSF');
    $router->get('/countCat', 'ContentController@countVideosCategory');
    $router->get('/likedVideo', 'ContentController@mostLikedVideos');
    $router->get('/viewedVideo', 'ContentController@mostViewedVideos');
    $router->post('/viewVideo', 'ContentController@videoView');
    $router->post('/getViewVideo', 'ContentController@getVideoView');
    
    //vidéos LPC
    $router->get('lpcvideo', 'ContentController@getLpcVideo');
    $router->post('/lpcvideo', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@addLpcVideo'
    ]);
    $router->delete('/lpcvideo', [
            'middleware' => 'role:30',
            'uses' =>    'ContentController@deleteLpcVideo'
        ]);
    
    $router->post('/addCategory', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@addCategory'
    ]);
    $router->get('/getVideos', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@getAllVideosContent'
    ]);
    $router->get('lpcvideo/{id}', 'ContentController@getLpcVideoById');
    
    //Devoirs
    $router->post('/devoirs', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@addDevoirs'
    ]);
    $router->get('/devoirs', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@getDevoirs'
    ]);
    $router->get('/thisdevoirs/{id}', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@getThisDevoirs'
    ]);
    $router->delete('/devoirs',  [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@deleteDevoir'
    ]);
    
    $router->get('/countOfflineVideos', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@countOfflineVideosLDSF'
    ]);

    $router->get('/countOfflineArticle', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@countOfflineArticle'
    ]);
    $router->get('/countOnlineArticle', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@countOnlineArticle'
    ]);
    $router->get('/countOnlinePublicArticle', 'ContentController@countOnlinePublicArticle');
    $router->get('/countMyArticle', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@countMyArticle'
    ]);
    //DIVERS
    //envoyer le mail de validation
    $router->post('/sendmail', 'UserController@sendMail');
    
    //Activer un utilisateur
    $router->put('/activate', 'UserController@activateUser');
    $router->put('/desactivate', 'UserController@desactivateUser');

    //Passer un article en On Line
    $router->put('/online', [
        'middleware' => 'role:80',
        'uses' =>    'ContentController@ArticleIsOnline'
    ]);
    //Passer un article en Off Line
    $router->put('/offline', [
        'middleware' => 'role:80',
        'uses' =>    'ContentController@ArticleIsOffline'
    ]);
    
    // Ajouter un carousel à un article
    $router->put('/carouselOn', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@CarousselOn'
    ]);
    // Supprimer le carousel d'un article
    $router->put('/carouselOff', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@CarousselOff'
    ]);

    $router->get('/countuser',  [
        'middleware' => 'role:30',
        'uses' => 'UserController@countUser'
    ]);
    $router->get('/countprouser',  [
        'middleware' => 'role:30',
        'uses' => 'UserController@countProUser'
    ]);
    $router->get('/countactiveuser',  [
        'middleware' => 'role:100',
        'uses' =>    'UserController@countActiveUser'
    ]);
    $router->post('/like',  [
        'middleware' => 'role:10',
        'uses' =>    'UserController@addlike'
    ]);
    $router->delete('/like',  [
        'middleware' => 'role:10',
        'uses' =>    'UserController@dislike'
    ]);
    $router->post('/likematch', 'UserController@likeMatch');
    // Gestion des scores des jeux
    $router->post('/memoryScores', 'ScoreController@getMemoryTopFiveScore');
    $router->post('/memoryScore', 'ScoreController@postMemoryScore');
    $router->post('/lsfplay', 'ScoreController@lsfGamePlay');
    $router->post('/lsfvictory', 'ScoreController@lsfGameVictory');
    $router->post('/mylsfscore', 'ScoreController@getMyLsfScore');
    $router->post('/lsftopten', 'ScoreController@getLsfTopTen');
    
    // QUIZZ et QUESTIONNAIRES
    $router->post('/question', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@addQuestion'
    ]);
    $router->post('/answer', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@addAnswer'
    ]);
    $router->get('/quizz/{id}', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@getQuizz'
    ]);
    $router->put('/question', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@updateQuestion'
    ]);
    $router->delete('/question', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@deleteQuizzQuestion'
    ]);
    $router->put('/answer', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@updateAnswer'
    ]);
    $router->delete('/answer', [
        'middleware' => 'role:30',
        'uses' =>    'ContentController@deleteQuizzAnswer'
    ]);
    //MESSAGES
    $router->post('/message', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@sendMessage'
    ]);
    $router->post('/messageMedia', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@sendMediaWithMessage'
    ]);
    
    $router->get('/message/{id}', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@getMessage'
    ]);
    $router->get('/messageUser', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@getUserMessageList'
    ]);
    $router->get('/userForMessage', [
        'middleware' => 'role:20',
        'uses' =>    'UserController@getUsersListForMessage'
    ]);
    $router->put('/isRead/{id}', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@messageIsRead'
    ]);
    $router->get('/countnomess', [
        'middleware' => 'role:20',
        'uses' =>    'ContentController@countNoReadMessage'
    ]);
    
    // ZONE DE TEST :)
    $router->get('/alert',  [
        'middleware' => 'role:100',
        'uses' =>    'UserController@passwordChangeAlert'
    ]);
});
