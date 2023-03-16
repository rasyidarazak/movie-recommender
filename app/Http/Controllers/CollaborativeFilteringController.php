<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CollaborativeFilteringController extends Controller
{
    public function index()
    {
        // Ambil data rating dari database
        $ratings = DB::table('ratings')->get();
        $users = [];
        $films = [];
        foreach ($ratings as $rating) {
            $user = $rating->user_id;
            $film = $rating->movie_id;
            $rating_value = $rating->rating;
            $users[$user][$film] = $rating_value;
            $films[$film][$user] = $rating_value;
        }

        // Temukan pengguna preferensi mirip dengan algoritma k-NN
        $k = 5; // Jumlah tetangga terdekat yang akan dicari
        $user = 1; // ID pengguna yang akan dicari rekomendasinya
        $neighbors = $this->find_similar_users($users, $user, $k);

        // Hitung rata-rata rating film dari pengguna preferensi mirip
        $film_ratings = [];
        foreach ($neighbors as $other_user => $similarity) {
            foreach ($films as $film => $users_ratings) {
                $rating = $users_ratings[$other_user] ?? null;
                if ($rating !== null) {
                    $film_ratings[$film][] = $rating;
                }
            }
        }
        $average_ratings = [];
        foreach ($film_ratings as $film => $ratings) {
            $average_ratings[$film] = array_sum($ratings) / count($ratings);
        }

        // Urutkan rekomendasi berdasarkan rating tertinggi
        arsort($average_ratings);

        // Ambil 5 rekomendasi teratas
        $recommendations = array_slice($average_ratings, 0, 5, true);

        // Hitung MAE (Mean Absolute Error) untuk pengguna tersebut
        $test_ratings = $users[$user];
        $predicted_ratings = [];
        foreach ($recommendations as $movie_id => $rating) {
            $predicted_ratings[$movie_id] = $rating;
        }
        $mae = $this->calculate_mae($test_ratings, $predicted_ratings);
        
        // Tampilkan rekomendasi dan MAE
        echo "Rekomendasi untuk pengguna $user:<br>";
        foreach ($recommendations as $movie_id => $rating) {
            $movie = DB::table('movies')->where('id', $movie_id)->first();
            $movie_name = $movie ? $movie->title : "Film tidak ditemukan";
            echo "- $movie_name dengan rating $rating <br>";
        }
        echo "MAE: $mae\n";
    }

    public function find_similar_users($users, $user_id, $k)
    {
        $similarities = [];
        $user_ratings = $users[$user_id];
        unset($users[$user_id]);
        foreach ($users as $other_user_id => $other_user_ratings) {
            $similarity = $this->cosine_similarity($user_ratings, $other_user_ratings);
            $similarities[$other_user_id] = $similarity;
        }
        arsort($similarities);
        $neighbors = array_slice($similarities, 0, $k, true);
        return $neighbors;
    }

    public function calculate_mae($test_ratings, $predicted_ratings)
    {
        $errors = [];
        foreach ($predicted_ratings as $movie_id => $predicted_rating) {
            $test_rating = $test_ratings[$movie_id] ?? null;
            if ($test_rating !== null) {
                $errors[] = abs($test_rating - $predicted_rating);
            }
        }
        if (count($errors) > 0) {
            $mae = array_sum($errors) / count($errors);
        } else {
            $mae = 0;
        }
        return $mae;
    }

    public function cosine_similarity($user1_ratings, $user2_ratings)
    {
        $dot_product = 0;
        $user1_norm = 0;
        $user2_norm = 0;
        foreach ($user1_ratings as $movie_id => $rating) {
            if (isset($user2_ratings[$movie_id])) {
                $dot_product += $rating * $user2_ratings[$movie_id];
            } else {
                $dot_product = $dot_product;
            }
            $user1_norm += pow($rating, 2);
        }
        foreach ($user2_ratings as $movie_id => $rating) {
            $user2_norm += pow($rating, 2);
        }
        $user1_norm = sqrt($user1_norm);
        $user2_norm = sqrt($user2_norm);
        if ($user1_norm == 0 || $user2_norm == 0) {
            return 0;
        } else {
            return $dot_product / ($user1_norm * $user2_norm);
        }
    }
}
