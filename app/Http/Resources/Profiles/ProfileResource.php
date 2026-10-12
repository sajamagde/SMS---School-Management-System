<?php

namespace App\Http\Resources\Profiles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'full_name' => $this['full_name'],
            'code' => $this['code'],
            'job' => $this['job'],
        ];

        if (array_key_exists('phone', $this->resource)) {
            $data['phone'] = $this['phone'];
        }
        if (array_key_exists('supervised_grade', $this->resource)) {
            $data['supervised_grade'] = $this['supervised_grade'];
            $data['sections_count'] = $this['sections_count'];
            $data['students_count'] = $this['students_count'];
            $data['sections'] = $this['sections'];
        }

        if (array_key_exists('supervised_grade', $this->resource)) {
            $data['supervised_grade'] = $this['supervised_grade'];
            $data['sections'] = $this['sections'];
        }

        if (array_key_exists('address', $this->resource)) {
            $data['address'] = $this['address'];
        }
        if (array_key_exists('teaching_information', $this->resource)) {
            $data['teaching_information'] =
                $this['teaching_information'];
        }

        if (array_key_exists('personal_information', $this->resource)) {
            $data['personal_information'] =
                $this['personal_information'];

            $data['parent'] = $this['parent'];
        }

        if (array_key_exists('student', $this->resource)) {
            $data['student'] = $this['student'];
        }

        return $data;
    }
}
