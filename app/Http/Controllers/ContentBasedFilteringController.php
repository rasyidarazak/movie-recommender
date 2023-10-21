<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ContentBasedFilteringController extends Controller
{
    public function index()
    {
        // Ambil data genre dari database
        $genres = DB::table('genres')->get();
        
        // Ambil data genre_movie dari database
        $genre_movies = DB::table('genre_movie')->get();
        $movies = [];
        foreach ($genre_movies as $genre_movie) {
            $movie_id = $genre_movie->movie_id;
            $genre_id = $genre_movie->genre_id;
            if (!isset($movies[$movie_id])) {
                $movies[$movie_id] = [];
            }
            $movies[$movie_id][$genre_id] = 1;
        }
        
        // Tentukan film yang akan direkomendasikan
        $movie_id = 525; // ID film yang akan dicari rekomendasinya
        $k = 5; // Jumlah tetangga terdekat yang akan dicari

        // Temukan film serupa dengan algoritma k-NN
        $similar_movies = $this->find_similar_movies($movies, $movie_id, $k);
        
        // Hitung rata-rata rating film serupa
        $genre_ratings = [];
        foreach ($similar_movies as $other_movie => $similarity) {
            foreach ($genres as $genre) {
                $genre_id = $genre->id;
                $rating = $movies[$other_movie][$genre_id] ?? null;
                if ($rating !== null) {
                    $genre_ratings[$genre_id][] = $rating;
                }
            }
        }
        
        $average_ratings = [];
        foreach ($genre_ratings as $genre_id => $ratings) {
            $average_ratings[$genre_id] = array_sum($ratings) / count($ratings);
        }

        // Urutkan rekomendasi berdasarkan rating tertinggi
        arsort($average_ratings);

        // Ambil 5 rekomendasi teratas
        $recommendations = array_slice($average_ratings, 0, 5, true);

        // Hitung MAE (Mean Absolute Error) untuk film tersebut
        if(isset($movies[$movie_id])){
            $test_ratings = $movies[$movie_id];
            $predicted_ratings = [];
            foreach ($recommendations as $genre_id => $rating) {
                $predicted_ratings[$genre_id] = $rating;
            }
            $mae = $this->calculate_mae($test_ratings, $predicted_ratings);
        } else {
            $mae = "Tidak dapat menghitung MAE, data tidak ditemukan";
        }

        // Ambil judul film dari tabel movies
        $movie_title = DB::table('movies')->where('id', $movie_id)->value('title');

        // Tampilkan rekomendasi dan MAE
        // echo "Rekomendasi untuk film dengan judul '$movie_title' (ID $movie_id):<br>";
        // foreach ($recommendations as $genre_id => $rating) {
        //     $genre = DB::table('genres')->where('id', $genre_id)->first();
        //     $genre_name = $genre ? $genre->genre : "Genre tidak ditemukan";
        //     echo "- $genre_name dengan rating $rating <br>";
        // }
        // echo "MAE: $mae\n";

        return view('content_based_filtering', compact('movie_title', 'movie_id', 'recommendations', 'mae'));
    }

    private function find_similar_movies($movies, $movie_id, $k)
    {
        // Cek apakah film memiliki genre atau tidak (Tabel genre_movie tidak lengkap)
        if (isset($movies[$movie_id])) {
            $movie_ratings = $movies[$movie_id];
        } else {
            $movie_ratings = [];
        }
        $similarities = [];
        foreach ($movies as $other_movie_id => $other_movie_ratings) {
            if ($other_movie_id != $movie_id) {
                $similarity = $this->cosine_similarity($movie_ratings, $other_movie_ratings);
                $similarities[$other_movie_id] = $similarity;
            }
        }
        arsort($similarities);
        return array_slice($similarities, 0, $k, true);
    }

    private function cosine_similarity($ratings1, $ratings2)
    {
        // Menyimpan hasil perkalian antara rating dari genre_movie 1 dan genre_movie 2 pada genre yang sama.
        $dot_product = 0;
        // Menyimpan nilai akar kuadrat dari jumlah kuadrat rating
        $magnitude1 = 0;
        $magnitude2 = 0;
        foreach ($ratings1 as $genre_id => $rating1) {
            if (isset($ratings2[$genre_id])) {
                $rating2 = $ratings2[$genre_id];
                $dot_product += $rating1 * $rating2;
            }
            $magnitude1 += $rating1 * $rating1;
        }
        foreach ($ratings2 as $genre_id => $rating2) {
            $magnitude2 += $rating2 * $rating2;
        }
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        return $dot_product / (sqrt($magnitude1) * sqrt($magnitude2));
    }

    private function calculate_mae($test_genres, $predicted_genres)
    {
        $sum_error = 0;
        $count_error = 0;

        foreach ($test_genres as $genre_id => $genre1) {
            // Cek apakah ada genre_movie yang berbeda pada genre sebenarnya dan genre prediksi
            if (!isset($predicted_genres[$genre_id])) {
                $genre2 = 0;
                $sum_error += abs($genre1 - $genre2);
            } else{
                $count_error += 1;
            }
        }
        if ($count_error == 0) {
            return 0;
        }
        return $sum_error / $count_error;
    }
}
