<?php



namespace App\Http\Controllers;



use App\Models\Content;

use App\Models\Paragraph;

use App\Models\ParagraphPhotos;

use App\Models\Photos;

use App\Models\Videos;

use App\Models\LongVideos;

use App\Models\Category;

use App\Models\Location;

use App\Models\ContentLike;

use App\Models\Views;

use App\Models\Questions;

use App\Models\Answers;

use App\Models\Message;

use App\Models\Devoirs;

use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;





class ContentController extends Controller

{



    public function __construct()

    {

        $this->middleware('auth:api', ['except' => ['getVideoCategory', 'searchLdsVideo', 'showVideo', 'getVideoByCat', 'getLdsVideoByIdContent', 'searchCity', 'countVideosLDSF', 'countVideosCategory', 'videoView', 'getVideoView', 'showPhoto', 'countOnlinePublicArticle', 'getPublicBlogArticle', 'countVideosForAccueil', 'getVideoByIdForAccueil', 'getAllVideo', 'getLpcVideo', 'getLpcVideoById']]);

    }

    // METHODES POUR L'ARTICLE COMPLET

    public function postBlogArticle(Request $request)

    {

        $this->validate($request, [

            'contentTitle' => 'required|regex:/^[^<>]+$/'

        ], [

            'contentTitle.required' => 'Un article doit obligatoirement avoir un titre.',

            'contentTitle.regex' => 'Merci de saisir correctement le nom de l\'article.'

        ]);

        $input = $request->only('contentTitle', 'contentType');



        try {

            $content = new Content;

            $content->contentTitle = htmlspecialchars($input['contentTitle']);

            $content->id_users = auth()->user()->id;

            $content->id_contentType = htmlspecialchars($input['contentType']);

            $content->save();

            return response()->json([

                'message' => 'Votre contenu a bien été créé',

                'Contenu' => $content

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre article', 500);

        }

    }



    public function updateBlogArticle(Request $request)

    {

        $this->validate($request, [

            'contentTitle' => 'required'

        ], [

            'contentTitle.required' => 'Un article doit obligatoirement avoir un titre.'

        ]);



        $input = $request->only('contentTitle');



        try {

            $content = new Content;

            $checkContent = Content::findOrFail($request->id);

            if ($checkContent->contentIsOnline == 0) {

                if ($checkContent->id_users == auth()->user()->id || auth()->user()->usergroup->id == 1) {

                    $content->where('id', $request->id)->update(array(

                        'contentTitle' => $input['contentTitle'],

                        'lastModifBy' => auth()->user()->id,

                    ));

                    return response()->json([

                        'message' => 'Vos informations ont bien été mise à jour',

                        'Description' => $content,

                    ], 201);

                } else {

                    return response()->json([

                        'message' => 'Seul le créateur de l\'article ou un administrateur peut le modifié.'

                    ], 403);

                }

            } else {

                return response()->json([

                    'message' => 'Un article en ligne ne peut plus être modifié.'

                ], 403);

            }

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de vos informations', 500);

        }

    }



    public function getBlogArticle($id)

    {

        try {

            $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'Like', 'View', 'ParagraphPhotos'])->where('contentIsOnline', 1)->findOrFail($id);

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getBlogArticleForBuild($id)

    {

        try {

            $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'Like', 'View', 'ParagraphPhotos'])->findOrFail($id);

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getAllBlogArticlesByUser()

    {

        try {

            $userId = ['id_users' => auth()->user()->id];

            $contentType = ['id_contentType' => '1', 'id_contentType' => '3'];

            $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'UserPhoto', 'Like', 'View', 'ParagraphPhotos'])->where($userId)->where(function ($q) {

                $q->where('id_contentType', '1')

                    ->orWhere('id_contentType', '3');

            })->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getAllBlogArticles()

    {

        try {

            if (auth()->user()->id_userGroup < 7) {

                $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'UserPhoto', 'Like', 'View', 'ParagraphPhotos'])->where('contentIsOnline', 1)->where(function ($q) {

                    $q->where('id_contentType', '1')

                        ->orWhere('id_contentType', '3');

                })->orderBy('contentDate', 'DESC')->limit(5)->get();

            } else {

                $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'UserPhoto', 'Like', 'View', 'ParagraphPhotos'])->where('contentIsOnline', 1)->where('id_contentType', '3')->orderBy('contentDate', 'DESC')->limit(5)->get();

            }

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getAllBlogArticlesForAdmin()

    {

        try {

            $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'UserPhoto', 'Like', 'View', 'ParagraphPhotos'])->where(function ($q) {

                $q->where('id_contentType', '1')

                    ->orWhere('id_contentType', '3');

            })->orderBy('contentDate', 'DESC')->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getAllBlogArticlesNoLimit()

    {

        try {

            if (auth()->user()->id_userGroup < 7) {

                $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'Like', 'View', 'ParagraphPhotos'])->where(function ($q) {

                $q->where('id_contentType', '1')

                    ->orWhere('id_contentType', '3');

            })->orderBy('contentDate', 'DESC')->where('contentIsOnline', 1)->get();

            } else {

                $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'Like', 'View', 'ParagraphPhotos'])->where('id_contentType', '3')->orderBy('contentDate', 'DESC')->where('contentIsOnline', 1)->get();

            }

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function searchBlogArticleByLike()

    {

        try {

            $video = DB::table('contentlike')

                ->join('content', 'content.id', '=', 'contentlike.id_content')

                ->join('userdescription', 'userdescription.id_users', '=', 'content.id_users')

                ->where('contentlike.id_users', auth()->user()->id)

                ->where('content.contentIsOnline', 1)

                ->where('content.id_contentType', 1)

                ->orWhere('content.id_contentType', '3')

                ->orderBy('content.contentDate', 'DESC')

                ->get(['contentlike.id_content', 'content.contentTitle', 'content.contentDate', 'userdescription.firstname', 'userdescription.lastname']);

            return response()->json($video, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function deleteBlogArticle(Request $request)

    {

        $input = $request->only('id');

        try {

            $content = new Content;

            $deleteContent = Content::findOrFail($input['id']);

            if ($deleteContent->contentIsOnline == 0) {

                if ($deleteContent->id_users == auth()->user()->id || auth()->user()->usergroup->id == 1) {

                    $content->where('id', $input['id'])->delete();

                    return response()->json([

                        'message' => 'L\'article a bien été supprimé'

                    ], 200);

                } else {

                    return response()->json([

                        'message' => 'Seul le créateur de l\'article ou un administrateur peut le supprimer.'

                    ], 403);

                }

            } else {

                return response()->json([

                    'message' => 'Un article en ligne ne peut plus être supprimé.'

                ], 403);

            }

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function ArticleIsOnline(Request $request)

    {

        $this->validate($request, [

            'contentIsOnline' => 'boolean'

        ]);

        try {

            $content = new Content;

            $content->where('id', $request->id)->update([

                'contentIsOnline' => 1,

                'onlineBy' => auth()->user()->id,

                'offlineBy' => null

            ]);

            return response()->json([

                'message' => 'Votre contenu est bien passé en online'

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre contenu', 500);

        }

    }



    public function ArticleIsOffline(Request $request)

    {

        $this->validate($request, [

            'contentIsOnline' => 'boolean'

        ]);

        try {

            $content = new Content;

            $content->where('id', $request->id)->update([

                'contentIsOnline' => 0,

                'offlineBy' => auth()->user()->id,

                'onlineBy' => null

            ]);

            return response()->json([

                'message' => 'Votre contenu est bien passé en offline'

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre contenu', 500);

        }

    }



    public function countOfflineArticle()

    {

        try {

            $content = Content::where(function ($q) {

                $q->where('id_contentType', '1')

                    ->orWhere('id_contentType', '3');

            })->where('contentIsOnline', 0)->get();

            return response()->json(count($content), 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function countOnlineArticle()

    {

        try {

            $content = Content::where(function ($q) {

                $q->where('id_contentType', '1')

                    ->orWhere('id_contentType', '3');

            })->where('contentIsOnline', 1)->get();

            return response()->json(count($content), 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function countOnlinePublicArticle()

    {

        try {

            $content = Content::where('id_contentType', '3')->where('contentIsOnline', 1)->get();

            return response()->json(['count' => count($content), 'content' => $content], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function getPublicBlogArticle($id)

    {

        try {

            $content = Content::with(['Paragraph', 'Photo', 'Video', 'User', 'UserDescription', 'Like', 'View', 'ParagraphPhotos'])->where('id_contentType', '3')->where('id', $id)->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function countMyArticle()

    {

        try {

            $myOnline = Content::where(function ($q) {

                $q->where('id_contentType', '1')

                    ->orWhere('id_contentType', '3');

            })->where('contentIsOnline', 1)->where('id_users', auth()->user()->id)->get();

            $myOffline = Content::where(function ($q) {

                $q->where('id_contentType', '1')

                    ->orWhere('id_contentType', '3');

            })->where('contentIsOnline', 0)->where('id_users', auth()->user()->id)->get();

            return response()->json(['online' => count($myOnline), 'offline' => count($myOffline)], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    // Ajouter / Retirer le carousel d'un article



    public function CarousselOn(Request $request)

    {

        $this->validate($request, [

            'carousel' => 'boolean'

        ]);

        try {

            $content = new Content;

            $content->where('id', $request->id)->update([

                'carousel' => 1

            ]);

            return response()->json([

                'message' => 'Un carousel a bien été ajouté à votre article.'

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant l\'ajout de votre carousel.', 500);

        }

    }



    public function CarousselOff(Request $request)

    {

        $this->validate($request, [

            'carousel' => 'boolean'

        ]);

        try {

            $content = new Content;

            $content->where('id', $request->id)->update([

                'carousel' => 0

            ]);

            return response()->json([

                'message' => 'Le carousel a bien été supprimé de votre article.'

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la suppression de votre carousel.', 500);

        }

    }



    public function getAllVideosContent()

    {

        try {

            $content = Content::with(['Video', 'category'])->where('id_contentType', 2)->orderByDesc('contentDate')->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    // FIN DES METHODES POUR L'ARTICLE COMPLET



    // METHODES POUR LES PARAGRAPHES



    public function addText(Request $request)

    {

        $this->validate($request, [

            'text' => 'required'

        ], [

            'text.required' => 'Merci de saisir du texte pour votre article.'

        ]);

        $input = $request->only('text', 'title');



        try {

            $paragraph = new Paragraph;

            $paragraph->title = $input['title'];

            $paragraph->text = $input['text'];

            $paragraph->id_content = $request->id_content;

            $paragraph->save();



            return response()->json($paragraph, 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre paragraphe', 500);

        }

    }



    public function getText($id)

    {

        try {

            $paragraph = Paragraph::where('id_content', $id)->get();

            return response()->json($paragraph, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function updateText(Request $request)

    {

        $this->validate($request, [

            'text' => 'required',

        ], [

            'text.required' => 'Merci de saisir du texte pour votre article.'

        ]);



        $input = $request->only('text', 'title', 'updated_at');



        try {

            $paragraph = new Paragraph;

            $paragraph->where('id', $request->id)->update([

                'text' => $input['text'],

                'title' => $input['title']

            ]);

            return response()->json([

                'message' => 'Vos informations ont bien été mise à jour',

                'contenu' => $paragraph

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre paragraphe', 500);

        }

    }



    public function deleteParagraph(Request $request)

    {

        try {

            $paragraph = new Paragraph;

            $paragraph->where('id', $request->id)->delete();

            return response()->json([

                'message' => 'L\'article a bien été supprimé',

                'Contenu' => $paragraph

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function addParagraphPhoto(Request $request)

    {

        $this->validate($request, [

            'photoTitle' => 'required',

            'photoText' => 'required',

            'file' => 'required|image:jpeg,png,jpg,gif,svg|max:1024',

            'id_paragraph' => 'required'

        ], [

            'photoTitle.required' => 'Le titre d\'une photo est obligatoire.',

            'photoText.required' => 'Merci de saisir un texte descriptif à votre photo.',

            'file.required' => 'Merci de selectionner un fichier photo.',

            'file.image' => 'Le fichier photo doit obligatoirement être en .jpeg, .png, .jpg, .gif ou .svg.',

            'file.max' => 'La taille du fichier ne doit pas dépasser 1024 ko.'

        ]);

        $input = $request->only('photoTitle', 'photoText', 'file', 'id_paragraph');



        try {

            if ($request->hasFile('file')) {

                $image = $request->file('file');

                $destination_path = './upload/blog/photos/';

                $image = 'BP-' . time() . '.' . $image->extension();

                $request->file('file')->move($destination_path, $image);

                $paragraphPhoto = new ParagraphPhotos;

                $paragraphPhoto->photoTitle = $input['photoTitle'];

                $paragraphPhoto->photoText = $input['photoText'];

                $paragraphPhoto->photoLink = '/upload/blog/photos/' . $image;

                $paragraphPhoto->fileName = $image;

                $paragraphPhoto->id_paragraph = $request->id_paragraph;

                $paragraphPhoto->save();



                return response()->json([

                    'message' => 'Votre contenu photo a bien été créé',

                    'user' => $paragraphPhoto

                ], 201);

            } else {

                return response()->json('Merci de sélectionner un fichier.', 403);

            }

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);

        }

    }



    public function deleteParagraphPhoto(Request $request)

    {

        $this->validate($request, [

            'fileName' => 'required'

        ], [

            'fileName.required' => 'Le nom de photo est obligatoire.'

        ]);

        $input = $request->only('fileName');



        try {

            $paragraphPhoto = new ParagraphPhotos;

            if (File::exists('./upload/blog/photos/' . $input['fileName'])) {

                File::delete('./upload/blog/photos/' . $input['fileName']);

            }

            $paragraphPhoto->where('fileName', $input['fileName'])->delete();

            return response()->json([

                'message' => 'Votre photo a bien été supprimée',

                'Contenu' => $paragraphPhoto

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Photo non trouvée', 404);

        }

    }

    // FIN DES METHODES POUR LES PARAGRAPHES



    // METHODES POUR LES PHOTOS



    public function addPhoto(Request $request)

    {

        $this->validate($request, [

            'photoTitle' => 'required',

            'photoText' => 'required',

            'file' => 'required|image:jpeg,png,jpg,gif,svg|max:2048',

            'id_content' => 'required'

        ], [

            'photoTitle.required' => 'Le titre d\'une photo est obligatoire.',

            'photoText.required' => 'Merci de saisir un texte descriptif à votre photo.',

            'file.required' => 'Merci de selectionner un fichier photo.',

            'file.image' => 'Le fichier photo doit obligatoirement être en .jpeg, .png, .jpg, .gif ou .svg.',

            'file.max' => 'La taille du fichier ne doit pas dépasser 1024 ko.'

        ]);

        $input = $request->only('photoTitle', 'photoText', 'file', 'id_content');



        try {

            if ($request->hasFile('file')) {

                $image = $request->file('file');

                $destination_path = './upload/blog/photos/';

                $image = 'BP-' . time() . '.' . $image->extension();

                $request->file('file')->move($destination_path, $image);

                $photo = new Photos;

                $photo->photoTitle = $input['photoTitle'];

                $photo->photoText = $input['photoText'];

                $photo->photoLink = '/upload/blog/photos/' . $image;

                $photo->fileName = $image;

                $photo->id_content = $request->id_content;

                $photo->save();



                return response()->json([

                    'message' => 'Votre contenu photo a bien été créé',

                    'user' => $photo

                ], 201);

            } else {

                return response()->json('Merci de sélectionner un fichier.', 403);

            }

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);

        }

    }



    public function updatePhoto(Request $request)

    {

        $this->validate($request, [

            'photoTitle' => 'required',

            'photoText' => 'required',

            'file' => 'required|image:jpeg,png,jpg,gif,svg|max:1024'

        ], [

            'photoTitle.required' => 'Le titre d\'une photo est obligatoire.',

            'photoText.required' => 'Merci de saisir un texte descriptif à votre photo.',

            'file.required' => 'Merci de selectionner un fichier photo.',

            'file.image' => 'Le fichier photo doit obligatoirement être en .jpeg, .png, .jpg, .gif ou .svg.',

            'file.max' => 'La taille du fichier ne doit pas dépasser 1024 ko.'

        ]);



        $input = $request->only('photoTitle', 'photoText', 'file', 'updated_at');



        try {

            $photo = new Photos;



            $photo->where('id', $request->id)->update([

                'photoTitle' => $input['photoTitle'],

                'photoText' => $input['photoText'],

                'photoLink' => $input['photoLink'],

            ]);

            return response()->json([

                'message' => 'Votre contenu photo a bien été mise à jour',

                'userDescription' => $photo

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre photo', 500);

        }

    }



    public function deletePhoto(Request $request)

    {

        try {

            $photo = new Photos;

            $photoToDelete = $photo->findOrFail($request->id);

            $photoToDelete->delete();

            if (File::exists('.' . $photoToDelete->photoLink)) {

                File::delete('.' . $photoToDelete->photoLink);

            }

            return response()->json([

                'message' => 'La photo a bien été supprimé',

                'Contenu' => $photoToDelete

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Photo non trouvé', 404);

        }

    }



    public function showPhoto($fileName)

    {

        try {

            $path = './upload/blog/photos/' . $fileName;

            if (!File::exists($path)) {

                abort(404);

            }

            $file = base64_encode(File::get($path));

            $type = File::mimeType($path);

            return response()->json(['file' => $file, 'type' => $type], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la récupération de la video', 500);

        }

    }



    // FIN DES METHODES POUR LES PHOTOS



    // METHODES POUR LES VIDEOS



    public function addCategory(Request $request)

    {

        $this->validate($request, [

            'category' => 'required|regex:/(^[a-zA-ZÀ-ÖØ-öø-ÿ]+)([- ]{1}[a-zA-ZÀ-ÖØ-öø-ÿ]+){0,3}$/'

        ], [

            'category.required' => 'Le nom d\'une catégorie est obligatoire.',

            'category.regex' => 'Le nom de la catégorie ne doit comporter que des lettres.'

        ]);

        $input = $request->only('category');

        try {

            $category = new Category;

            $category->category = htmlspecialchars($input['category']);

            $category->save();

            return response()->json([

                'message' => 'Votre catégorie a bien été créé',

                'user' => $category

            ], 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre paragraphe', 500);

        }

    }



    public function getVideoCategory()

    {

        try {

            $category = Category::with(['Video', 'Content'])->get();

            return response()->json($category, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getVideoByCat(Request $request)

    {

        try {

            $input = $request->only('id_category');

            $category = htmlspecialchars($input['id_category']);

            $video = DB::table('videos')

                ->join('category', 'category.id', '=', 'videos.id_category')

                ->join('content', 'content.id', '=', 'videos.id_content')

                ->where('videos.id_category', $category)

                ->where('content.contentIsOnline', 1)

                ->get(['videos.id_content', 'videos.videoTitle', 'content.contentIsOnline', 'videos.videoLink']);

            return response()->json($video, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getAllVideo()

    {

        try {

            $content = Content::with(['Video', 'Like', 'Category', 'View'])->where('id_contentType', 2)->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }

    

    public function getLpcVideo()

    {

        try {

            $content = Content::with(['LongVideo', 'Like', 'View'])->where('id_contentType', 5)->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getVideoById($id)

    {

        try {

            $content = Content::with(['Video', 'Like', 'Category', 'View'])->where('id_contentType', 2)->where('id', $id)->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getVideoByIdForAccueil($id)

    {

        try {

            $content = Content::with(['Video', 'Like', 'Category', 'View'])->where('id_contentType', 2)->where('id', $id)->get();

            return response()->json(['content' => $content], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }

    

    public function getLpcVideoById($id)

    {

        try {

            $content = Content::with(['LongVideo', 'Like', 'View'])->where('id_contentType', 5)->where('id', $id)->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function searchLdsVideo(Request $request)

    {

        try {

            $input = $request->only('contentTitle');

            $title = htmlspecialchars($input['contentTitle']);

            $content = Content::with(['Video', 'Like', 'Category', 'View'])->where('id_contentType', 2)->where('contentIsOnline', 1)->where('contentTitle', 'LIKE', '%' . $title . '%')->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function searchLdsVideoByLike()

    {

        try {

            $video = DB::table('contentlike')

                ->join('content', 'content.id', '=', 'contentlike.id_content')

                ->join('videos', 'videos.id_content', '=', 'contentlike.id_content')

                ->join('category', 'category.id', '=', 'videos.id_category')

                ->where('contentlike.id_users', auth()->user()->id)

                ->where('content.contentIsOnline', 1)

                ->orderBy('category')

                ->get(['contentlike.id_users', 'contentlike.id_content', 'contentTitle', 'videoText', 'fileName', 'videoTitle', 'id_category', 'category']);

            return response()->json($video, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function mostLikedVideos()

    {

        try {

            $count = ContentLike::count();

            $videoList = DB::table('contentlike')

                ->join('content', 'content.id', '=', 'contentlike.id_content')

                ->join('videos', 'videos.id_content', '=', 'contentlike.id_content')

                ->join('category', 'category.id', '=', 'videos.id_category')

                ->selectRaw('contentlike.id_content, count(contentlike.id_content) as numLike')

                ->selectRaw('contentTitle')

                ->selectRaw('videoText')

                ->selectRaw('fileName')

                ->selectRaw('id_category')

                ->selectRaw('category')

                ->groupBy('contentlike.id_content', 'contentTitle', 'videoText', 'fileName', 'id_category', 'category')

                ->orderByDesc('numLike')

                ->limit(3)

                ->get();

            // $content = ContentLike::with(['Video', 'Content', 'Category'])->where('id_contentType', 2)->get();



            return response()->json($videoList, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function mostViewedVideos()

    {

        try {

            $count = ContentLike::count();

            $videoList = DB::table('content')

                ->join('videos', 'videos.id_content', '=', 'content.id')

                ->join('category', 'category.id', '=', 'videos.id_category')

                ->join('views', 'views.id_content', '=', 'content.id')

                ->orderByDesc('views.viewNumber')

                ->limit(3)

                ->get();

            // $content = ContentLike::with(['Video', 'Content', 'Category'])->where('id_contentType', 2)->get();



            return response()->json($videoList, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function searchLdsVideoByCatName(Request $request)

    {

        try {

            $input = $request->only('category');

            $cat = htmlspecialchars($input['category']);

            $video = DB::table('contentlike')

                ->join('content', 'content.id', '=', 'contentlike.id_content')

                ->join('videos', 'videos.id_content', '=', 'contentlike.id_content')

                ->join('category', 'category.id', '=', 'videos.id_category')

                ->where('contentlike.id_users', auth()->user()->id)

                ->where('category', 'LIKE', $cat)

                ->where('content.contentIsOnline', 1)

                ->orderBy('category')

                ->get(['contentlike.id_users', 'contentlike.id_content', 'contentTitle', 'videoText', 'fileName', 'videoTitle', 'id_category', 'category']);

            return response()->json($video, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function getLdsVideoByIdContent(Request $request)

    {

        try {

            $input = $request->only('id');

            $id = htmlspecialchars($input['id']);

            $content = Content::with(['Video', 'Like', 'Category', 'View'])->where('id_contentType', 2)->where('contentIsOnline', 1)->where('id', $id)->get();

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function countVideosLDSF()

    {

        try {

            $content = Content::where('id_contentType', 2)->where('contentIsOnline', 1)->get();

            return response()->json(count($content), 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function countVideosForAccueil()

    {

        try {

            $content = Content::where('id_contentType', 2)->where('contentIsOnline', 1)->get();

            return response()->json(['count' => count($content), 'content' => $content], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function countMyVideosLDSF()

    {

        try {

            $myOnlineContent = Content::where('id_contentType', 2)->where('contentIsOnline', 1)->where('id_users', auth()->user()->id)->get();

            $myOfflineContent = Content::where('id_contentType', 2)->where('contentIsOnline', 0)->where('id_users', auth()->user()->id)->get();

            return response()->json(['online' => count($myOnlineContent), 'offline' => count($myOfflineContent)], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function countOfflineVideosLDSF()

    {

        try {

            $content = Content::where('id_contentType', 2)->where('contentIsOnline', 0)->get();

            return response()->json(count($content), 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function countVideosCategory()

    {

        try {

            $content = Category::with(['Content'])->get();

            return response()->json(count($content), 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Comptage non possible', 404);

        }

    }



    public function addVideo(Request $request)

    {

        $this->validate($request, [

            'videoTitle' => 'required|regex:/^[^<>]+$/',

            'videoText' => 'required|regex:/^[^<>]+$/',

            'file' => 'required|mimes:mp4,avi,mov|max:20024',

            'id_content' => 'required',

            'id_category' => 'required'

        ], [

            'videoTitle.required' => 'Le titre d\'une vidéo est obligatoire.',

            'videoText.required' => 'Merci de saisir un texte descriptif à votre vidéo.',

            'file.required' => 'Merci de selectionner un fichier vidéo.',

            'file.mimes' => 'Le fichier vidéo doit obligatoirement être en .mp4, .avi ou .mov.',

            'file.max' => 'La taille du fichier ne doit pas dépasser 20024 ko.'

        ]);

        $input = $request->only('videoTitle', 'videoText', 'file', 'id_content');

        try {

            if ($request->hasFile('file')) {

                $image = $request->file('file');

                $destination_path = './upload/blog/videos/';

                $image = 'BV-' . time() . '.' . $image->extension();

                $request->file('file')->move($destination_path, $image);

                $video = new Videos;

                $video->videoTitle = htmlspecialchars($input['videoTitle']);

                $video->videoText = htmlspecialchars($input['videoText']);

                $video->videoLink = '/upload/blog/videos/' . $image;

                $video->fileName = $image;

                $video->id_content = $request->id_content;

                $video->id_category = $request->id_category;

                $video->save();

                return response()->json([

                    'message' => 'Votre contenu vidéo a bien été créé',

                    'Video' => $video

                ], 201);

            } else {

                return response()->json('Merci de sélectionner un fichier.', 403);

            }

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre contenu vidéo', 500);

        }

    }

    

    public function addLpcVideo(Request $request)

    {

        $this->validate($request, [

            'videoTitle' => 'required|regex:/^[^<>]+$/',

            'file' => 'required|mimes:mp4,avi,mov,m4v',

            'id_content' => 'required'

        ], [

            'videoTitle.required' => 'Le titre d\'une vidéo est obligatoire.',

            'videoText.required' => 'Merci de saisir un texte descriptif à votre vidéo.',

            'file.required' => 'Merci de selectionner un fichier vidéo.',

            'file.mimes' => 'Le fichier vidéo doit obligatoirement être en .mp4, .avi ou .mov.'

        ]);

        $input = $request->only('videoTitle', 'file', 'id_content');

        try {

            if ($request->hasFile('file')) {

                $image = $request->file('file');

                $destination_path = './upload/lpc/videos/';

                $image = 'LPC-' . time() . '.' . $image->extension();

                $request->file('file')->move($destination_path, $image);

                $video = new LongVideos;

                $video->videoTitle = htmlspecialchars($input['videoTitle']);

                $video->videoLink = '/upload/lpc/videos/' . $image;

                $video->id_content = $request->id_content;

                $video->save();

                return response()->json([

                    'message' => 'Votre contenu vidéo a bien été créé',

                    'Video' => $video

                ], 201);

            } else {

                return response()->json('Merci de sélectionner un fichier.', 403);

            }

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre contenu vidéo', 500);

        }

    }



    public function showVideo($fileName)

    {

        try {

            $path = './upload/blog/videos/' . $fileName;

            if (!File::exists($path)) {

                abort(404);

            }

            $file = base64_encode(File::get($path));

            $type = File::mimeType($path);

            return response()->json(['file' => $file, 'type' => $type], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la récupération de la video', 500);

        }

    }



    public function updateVideo(Request $request)

    {

        $this->validate($request, [

            'videoTitle' => 'required',

            'videoText' => 'required',

            'videoLink' => 'required|URL'

        ]);



        $input = $request->only('videoTitle', 'videoText', 'videoLink', 'updated_at');



        try {

            $video = new Videos;

            $video->where('id', $request->id)->update([

                'videoTitle' => $input['videoTitle'],

                'videoText' => $input['videoText'],

                'videoLink' => $input['videoLink']

            ]);

            return response()->json([

                'message' => 'Votre contenu photo a bien été mise à jour',

                'userDescription' => $video

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre photo', 500);

        }

    }



    public function deleteVideo(Request $request)

    {

        $input = $request->only('id');

        try {

            $video = new Videos;

            $videoToDelete = $video->findOrFail($input['id']);

            $videoToDelete->delete();

            if (File::exists('.' . $videoToDelete->videoLink)) {

                File::delete('.' . $videoToDelete->videoLink);

            }

            return response()->json([

                'message' => 'La vidéo a bien été supprimée',

                'Contenu' => $videoToDelete

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Vidéo non trouvé', 404);

        }

    }

    

    public function deleteLpcVideo(Request $request)

    {

        $input = $request->only('id');

        try {

            $video = new LongVideos;

            $videoToDelete = $video->findOrFail($input['id']);

            $videoToDelete->delete();

            if (File::exists('.' . $videoToDelete->videoLink)) {

                File::delete('.' . $videoToDelete->videoLink);

            }

            return response()->json([

                'message' => 'La vidéo a bien été supprimée',

                'Contenu' => $videoToDelete

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Vidéo non trouvé', 404);

        }

    }



    // METHODE DE COMPTAGE DES VUES D'UNE VIDEO

    public function videoView(Request $request)

    {

        $this->validate($request, [

            'id_content' => 'required'

        ], [

            'id_content.required' => 'Le titre d\'id_content est obligatoire.'

        ]);

        $input = $request->only('id_content');

        try {

            $views = new Views;

            $views->updateOrInsert([

                'id_content' => $input['id_content']

            ])->where('id_content', $input['id_content'])->increment('viewNumber', 1);

            return response()->json($views, 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant l\'ajout de la vue', 500);

        }

    }



    public function getVideoView(Request $request)

    {

        $this->validate($request, [

            'id_content' => 'required'

        ], [

            'id_content.required' => 'Le titre d\'id_content est obligatoire.'

        ]);

        $input = $request->only('id_content');

        try {

            $views = Views::where('id_content', $input['id_content'])->get();

            return response()->json($views, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant l\'affichage des vues', 404);

        }

    }



    // FIN DES METHODES POUR LES VIDEOS



    // METHODE POUR RECHERCHER LES VILLES



    public function searchCity(Request $request)

    {

        try {

            $input = $request->only('postalCode');

            $postalCode = htmlspecialchars($input['postalCode']);

            $city = Location::where('postalCode', 'LIKE', $postalCode . '%')->limit(100)->get();

            return response()->json($city, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Ville non trouvée', 404);

        }

    }

    

        // METHODES POUR LE QUIZZ ET LE QUESTIONNAIRE DE SATISFACTION //



    // QUIZZ //



    public function addQuestion(Request $request)

    {

        $this->validate($request, [

            'question' => 'required'

        ], [

            'question.required' => 'Merci de saisir une question.'

        ]);

        $input = $request->only('question');



        try {

            $question = new Questions;

            $question->question = $input['question'];

            if ($request->hasFile('file')) {

                $image = $request->file('file');

                $destination_path = './upload/quizz/photos/';

                $image = 'QP-' . time() . '.' . $image->extension();

                $request->file('file')->move($destination_path, $image);

                $question->photoLink = '/upload/quizz/photos/' . $image;

                $question->fileName = $image;

            }

            $question->id_content = $request->id_content;

            $question->save();



            return response()->json($question, 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre question', 500);

        }

    }



    public function addAnswer(Request $request)

    {

        $this->validate($request, [

            'answer' => 'required'

        ], [

            'answer.required' => 'Merci de saisir une réponse.'

        ]);

        $input = $request->only('answer');



        try {

            $answer = new Answers;

            $answer->answer = $input['answer'];

            $answer->isGood = $request->isGood;

            $answer->comment = $request->comment;

            $answer->id_questions = $request->id_questions;

            $answer->save();



            return response()->json([

                'message' => 'Votre question a bien été créé',

                'user' => $answer

            ], 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre question', 500);

        }

    }





    public function getQuizz($id)

    {

        try {

            $content = Content::with(['Questions', 'Answers', 'User', 'UserDescription'])->where('contentIsOnline', 0)->where('id_contentType', 4)->findOrFail($id);

            return response()->json($content, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }



    public function updateQuestion(Request $request)

    {

        $this->validate($request, [

            'question' => 'required'

        ], [

            'question.required' => 'Merci de saisir du texte pour votre question.'

        ]);



        $input = $request->only('question');



        try {

            $question = new Questions;

            $question->where('id', $request->id)->update([

                'question' => $input['question']

            ]);

            return response()->json([

                'message' => 'Vos informations ont bien été mise à jour',

                'contenu' => $question

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre paragraphe', 500);

        }

    }



    public function deleteQuizzQuestion(Request $request)

    {

        try {

            $question = new Questions;

            $questionToDelete = $question->findOrFail($request->id);

            $questionToDelete->delete();

            if (File::exists('.' . $questionToDelete->photoLink)) {

                File::delete('.' . $questionToDelete->photoLink);

            }

            return response()->json([

                'message' => 'La question a bien été supprimé',

                'Contenu' => $questionToDelete

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Question non trouvé', 404);

        }

    }



    public function updateAnswer(Request $request)

    {

        $this->validate($request, [

            'answer' => 'required'

        ], [

            'answer.required' => 'Merci de saisir du texte pour votre question.'

        ]);



        $input = $request->only('answer');



        try {

            $answer = new Answers;

            $answer->where('id', $request->id)->update([

                'answer' => $input['answer'],

                'isGood' => $request->isGood,

                'comment' => $request->comment

            ]);

            return response()->json([

                'message' => 'Vos informations ont bien été mise à jour',

                'contenu' => $answer

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre paragraphe', 500);

        }

    }



    public function deleteQuizzAnswer(Request $request)

    {

        try {

            $answer = new Answers;

            $answerToDelete = $answer->findOrFail($request->id);

            $answerToDelete->delete();

            return response()->json([

                'message' => 'La question a bien été supprimé',

                'Contenu' => $answerToDelete

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Réponse non trouvé', 404);

        }

    }

    // MESSAGES



    public function sendMessage(Request $request)

    {

        $this->validate($request, [

            'toUser' => 'required',

            'content' => 'required',

            'file' => 'image:jpeg,png,jpg,gif,svg|mimes:mp4,avi,mov',

        ], [

            'toUser.required' => 'Merci de sélectionner la personne à qui vous voulez envoyer le message.',

            'content.required' => 'Merci de saisir un message.'

        ]);

        $input = $request->only('toUser', 'content');



        try {

            $message = new Message;

            $message->toUser = $input['toUser'];

            $message->content = $input['content'];

            $message->id_users = auth()->user()->id;

            $message->fromUser = auth()->user()->id;

            $message->save();



            return response()->json([

                'message' => 'Votre message a bien été envoyé',

                'user' => $message

            ], 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant l\'envoi de votre message.', 500);

        }

    }

    

    public function sendMediaWithMessage(Request $request)

    {

        $this->validate($request, [

            'toUser' => 'required',

            'content' => 'required'

        ], [

            'toUser.required' => 'Merci de sélectionner la personne à qui vous voulez envoyer le message.',

            'content.required' => 'Merci de saisir un message.'

        ]);

        $input = $request->only('toUser', 'content');



        try {

            $message = new Message;

            $message->toUser = $input['toUser'];

            $message->content = $input['content'];

            $message->id_users = auth()->user()->id;

            $message->fromUser = auth()->user()->id;

            if ($request->hasFile('file')) {

                $image = $request->file('file');

                $destination_path = './upload/message/media/';

                $image = 'MM-' . time() . '.' . $image->extension();

                $request->file('file')->move($destination_path, $image);

                $message->mediaLink = '/upload/message/media/' . $image;

            };

            $message->save();



            return response()->json([

                'message' => 'Votre message a bien été envoyé',

                'user' => $message

            ], 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant l\'envoi de votre message.', 500);

        }

    }

    



    public function getMessage($id)

    {

        try {

            $message = Message::with(['User', 'UserDescription'])->where('id_users', auth()->user()->id)->where('toUser', $id)->orWhere('fromUser', $id)->get();

            return response()->json($message, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }

    

    public function getUserMessageList()

    {

        try {

            $message = Message::with(['User', 'DestiUser', 'UserDescription', 'DestiUserDescription'])->where('toUser', auth()->user()->id)->orWhere('id_users', auth()->user()->id)->get();

            return response()->json($message, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }

    

    public function countNoReadMessage()

    {

        try {

            $messageNoRead = Message::with(['User', 'DestiUser', 'UserDescription', 'DestiUserDescription'])->where('isRead', 0)->where('fromUser', '!=' , auth()->user()->id)->get();

            return response()->json(count($messageNoRead), 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }

    

    public function messageIsRead($id)

    {

        try {

            $message = new Message;

            $message->where('id_users', '!=', auth()->user()->id)->where('toUser', auth()->user()->id)->where('id_users', $id)->update([

                'isRead' => 1,

                'isReadTime' =>Carbon::now()

            ]);

            return response()->json([

                'message' => 'Vos informations ont bien été mise à jour',

                'contenu' => $message

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la modification de votre paragraphe', 500);

        }

    }

    

    // devoirs

    public function addDevoirs(Request $request)

    {

        $this->validate($request, [

            'content' => 'required',

            'title' => 'required',

            'dateFor' => 'required'

        ], [

            'content.required' => 'Merci de saisir du texte pour votre devoir.',

            'title.required' => 'Merci de saisir du titre pour votre devoir.',

            'dateFor.required' => 'Merci de saisir une date de fin pour votre devoir.'

        ]);

        $input = $request->only('content', 'title', 'dateFor');



        try {

            $devoir = new Devoirs;

            $devoir->title = $input['title'];

            $devoir->content = $input['content'];

            $devoir->dateFor = $input['dateFor'];

            $devoir->id_content = $request->id_content;

            $devoir->save();



            return response()->json($devoir, 201);

        } catch (ModelNotFoundException $e) {

            return response()->json('Une erreur est survenue durant la création de votre paragraphe', 500);

        }

    }

    

    public function getDevoirs()

    {

        try {

            $devoir = Content::with(['Devoirs', 'User', 'UserDescription', 'Like', 'View'])->where('id_contentType', 6)->get();

            return response()->json($devoir, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }

    

    public function getThisDevoirs($id)

    {

        try {

            $devoir = Content::with(['Devoirs', 'User', 'UserDescription', 'Like', 'View'])->where('id_contentType', 6)->where('id', $id)->get();

            return response()->json($devoir, 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Article non trouvé', 404);

        }

    }

    

    public function deleteDevoir(Request $request)

    {

        $input = $request->only('id');

        try {

            $devoir = new Devoirs;

            $devoirToDelete = $devoir->findOrFail($input['id']);

            $devoirToDelete->delete();

            return response()->json([

                'message' => 'La vidéo a bien été supprimée',

                'Contenu' => $devoirToDelete

            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json('Vidéo non trouvé', 404);

        }

    }

}

