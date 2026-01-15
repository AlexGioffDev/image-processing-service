<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class PhotoController extends Controller
{



    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048'
        ]);

        $photid = uniqid();
        $photoFolder = "photos/{$photid}";

        $file = $request->file('photo');
        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs($photoFolder, $originalName, 'public');
        $photo = Photo::create([
            'user_id' => $request->user()->id,
            'path' => $path,
            'disk' => 'public',
            'photo_folder' => $photid,
            'original_name' => $originalName
        ]);



        return response()->json($photo, 201);
    }

    public function destroy(Photo $photo)
    {
        if (Auth::user()->cannot('delete', $photo)) {
            return response()->json(['error' => 'You can\'t delete this photo '], 403);
        }

        if ($photo->isOriginal()) {
            $folderPath = "photos/{$photo->photo_folder}";
            Storage::disk($photo->disk)->deleteDirectory($folderPath);

            $photo->delete();
        }
        else
        {
            Storage::disk($photo->disk)->delete($photo->path);
            $photo->delete();
        }
        return response()->json([
            'Message' => 'Photo deleted'
        ]);
    }

    public function index(Request $request)
    {
        $photos = Photo::where('user_id', $request->user()->id)
        ->whereNull('parent_photo_id')
        ->with('transformedVersions')
        ->latest()
        ->paginate(10);

        return response()->json($photos);
    }

    public function transform(Request $request, Photo $photo)
    {
        if(Auth::user()->cannot('transform', $photo))
        {
            return response()->json(['error' => 'You can\'t delete this photo'], 403);
        }

        $manager = new ImageManager(new Driver());

        $request->validate([
            'transformations' => 'required|array',
            'transformations.resize' => 'sometimes|array',
            'transformations.resize.width' => 'sometimes|integer|min:1',
            'transformations.resize.height' => 'sometimes|integer|min:1',
            'transformations.rotate' => 'sometimes|integer|min:0|max:360',
            'transformations.crop' => 'sometimes|array',
            'transformations.crop.width' => 'required_with:transformations.crop|integer',
            'transformations.crop.height' => 'required_with:transformations.crop|integer',
            'transformations.crop.x' => 'required_with:transformations.crop|integer',
            'transformations.crop.y' => 'required_with:transformations.crop|integer',
            'transformations.filters' => 'sometimes|array',
        ]);


        $originalPath = Storage::disk($photo->disk)->path($photo->path);
        $image = $manager->read($originalPath);

        $transformations = $request->transformations;

        if (isset($transformations['resize'])) {
            $width = $transformations['resize']['width'] ?? null;
            $height = $transformations['resize']['height'] ?? null;

            if ($width && $height) {
                $image->resize($width, $height);
            } elseif ($width) {
                $image->scale(width: $width);
            } elseif ($height) {
                $image->scale(height: $height);
            }
        }

        // CROP
        if (isset($transformations['crop'])) {
            $image->crop(
                $transformations['crop']['width'],
                $transformations['crop']['height'],
                $transformations['crop']['x'],
                $transformations['crop']['y']
            );
        }

        // ROTATE
        if (isset($transformations['rotate'])) {
            $image->rotate($transformations['rotate']);
        }

        // FLIP (orizzontale)
        if (!empty($transformations['flip'])) {
            $image->flip();
        }

        // MIRROR (verticale)
        if (!empty($transformations['mirror'])) {
            $image->flop();
        }

        // FILTERS
        if (isset($transformations['filters'])) {
            if (!empty($transformations['filters']['grayscale'])) {
                $image->greyscale();
            }

            if (isset($transformations['filters']['blur'])) {
                $blur = $transformations['filters']['blur'];
                $image->blur($blur);
            }

            if (isset($transformations['filters']['brightness'])) {
                $brightness = $transformations['filters']['brightness'];
                $image->brightness($brightness);
            }

            if (isset($transformations['filters']['contrast'])) {
                $contrast = $transformations['filters']['contrast'];
                $image->contrast($contrast);
            }
        }

        // Crea cartella transform
        $transformFolder = "photos/{$photo->photo_folder}/transform";
        Storage::disk('public')->makeDirectory($transformFolder);

        // Nome file trasformato
        $fileName = pathinfo($photo->original_name, PATHINFO_FILENAME);
        $extension = pathinfo($photo->original_name, PATHINFO_EXTENSION);
        $transformedName = "{$fileName}_edit_" . time() . ".{$extension}";
        $transformedPath = "{$transformFolder}/{$transformedName}";

        // Salva immagine trasformata
        $fullPath = Storage::disk('public')->path($transformedPath);
        $image->save($fullPath);

        // Crea record per versione trasformata
        $transformedPhoto = Photo::create([
            'user_id' => $photo->user_id,
            'path' => $transformedPath,
            'disk' => 'public',
            'photo_folder' => $photo->photo_folder,
            'original_name' => $transformedName,
            'parent_photo_id' => $photo->id,
            'transformations' => $transformations
        ]);

        $transformedPhoto->load('parent');

        return response()->json($transformedPhoto);
    }
}
