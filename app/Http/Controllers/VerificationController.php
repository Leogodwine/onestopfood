<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChefProfile;
use App\Models\TravelerProfile;

class VerificationController extends Controller
{
    /**
     * Show the verification form based on the user's role.
     */
    public function showVerificationForm()
    {
        $user = Auth::user();

        if ($user->status !== User::STATUS_PENDING && $user->status !== User::STATUS_REJECTED && $user->status !== null && $user->status !== '') {
            // Already approved or suspended, or another status
            // Depending on the exact logic of "not started", usually it's null or 'incomplete'
            // We assume if status is approved they shouldn't see this form.
        }

        if ($user->role === User::ROLE_CHEF) {
            $profile = $user->chefProfile ?? new ChefProfile();
            return view('profile.verify_chef', compact('user', 'profile'));
        } elseif ($user->role === User::ROLE_TRAVELER) {
            $profile = $user->travelerProfile ?? new TravelerProfile();
            return view('profile.verify_traveler', compact('user', 'profile'));
        }

        return redirect()->route('dashboard')->with('error', 'Invalid role for verification.');
    }

    /**
     * Handle the submission of the verification form.
     */
    public function submitVerificationForm(Request $request)
    {
        $user = Auth::user();
        
        $isFinalSubmission = $request->has('tos_agreement');

        // Base validation rules
        $rules = [
            'dob' => ['nullable', 'date', 'before:-18 years'],
            'nida_id' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'selfie' => ['nullable', 'image', 'max:10240'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'street_address' => ['nullable', 'string', 'max:500'],
            'city_district' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'proof_of_address' => ['nullable', 'file', 'max:10240'],
        ];

        if ($user->role === User::ROLE_CHEF) {
            $rules = array_merge($rules, [
                'bio' => ['nullable', 'string', 'min:50'],
                'years_experience' => ['nullable', 'string'],
                'address_type' => ['nullable', 'string'],
                'kitchen_photos.*' => ['nullable', 'image', 'max:10240'],
                'professional_training' => ['nullable', 'file', 'max:10240'],
                'food_safety_cert' => ['nullable', 'file', 'max:10240'],
                'business_license' => ['nullable', 'file', 'max:10240'],
                'food_handling_permit' => ['nullable', 'file', 'max:10240'],
                'health_inspection_cert' => ['nullable', 'file', 'max:10240'],
            ]);
        } elseif ($user->role === User::ROLE_TRAVELER) {
            $rules = array_merge($rules, [
                'vehicle_type' => ['nullable', 'string'],
                'license_number' => ['nullable', 'string'],
                'vehicle_photo' => ['nullable', 'image', 'max:10240'],
                'vehicle_proof_of_ownership' => ['nullable', 'file', 'max:10240'],
                'vehicle_insurance' => ['nullable', 'file', 'max:10240'],
            ]);
        }

        if ($isFinalSubmission) {
            $requiredFields = [
                'dob', 'nida_id', 'nationality', 'gender', 
                'street_address', 'city_district', 
                'bank_name', 'account_number', 'account_holder_name',
                'background_check_consent', 'tos_agreement', 'code_of_conduct_agreement', 'criminal_record_declaration'
            ];
            
            if ($user->role === User::ROLE_CHEF) {
                $requiredFields = array_merge($requiredFields, ['bio', 'years_experience', 'address_type', 'ward_neighborhood']);
            } elseif ($user->role === User::ROLE_TRAVELER) {
                $requiredFields = array_merge($requiredFields, ['license_number']);
            }

            foreach ($requiredFields as $field) {
                if (isset($rules[$field])) {
                    $rules[$field] = array_values(array_filter($rules[$field], fn($r) => $r !== 'nullable'));
                    array_unshift($rules[$field], 'required');
                } else {
                    $rules[$field] = ['required'];
                }
            }
        }

        $request->validate($rules);

        $input = $request->except(['_token', 'active_tab']);
        
        // We track all raw file input names in the form
        $fileInputNames = [
            'selfie', 'proof_of_address', 'professional_training', 'food_safety_cert',
            'business_license', 'food_handling_permit', 'health_inspection_cert',
            'vehicle_photo', 'vehicle_proof_of_ownership', 'vehicle_insurance',
            'kitchen_photos' // used as array
        ];

        $fileFields = [
            'selfie' => 'selfie_path',
            'proof_of_address' => 'proof_of_address_path',
            'professional_training' => 'professional_training_path',
            'food_safety_cert' => 'food_safety_cert_path',
            'business_license' => 'business_license_path',
            'food_handling_permit' => 'food_handling_permit_path',
            'health_inspection_cert' => 'health_inspection_cert_path',
            'vehicle_photo' => 'vehicle_photo_path',
            'vehicle_proof_of_ownership' => 'vehicle_proof_of_ownership_path',
            'vehicle_insurance' => 'vehicle_insurance_path',
        ];

        // Remove raw file inputs from $input to prevent null overwrites of JSON/Array columns
        foreach ($fileInputNames as $fName) {
            unset($input[$fName]);
        }

        // Process files that were actually uploaded
        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                $paths = [];
                foreach ($file as $f) {
                    $paths[] = $f->store('verifications', 'public');
                }
                $input[$key] = $paths; // Assign paths back to the JSON casting column
            } else {
                $path = $file->store('verifications', 'public');
                if (isset($fileFields[$key])) {
                    $input[$fileFields[$key]] = $path; // Map to the `_path` column
                } else {
                    $input[$key] = $path;
                }
            }
        }

        // Handle checkbox booleans specifically to ensure false if missing
        $checkboxes = [
            'background_check_consent', 'tos_agreement', 'code_of_conduct_agreement', 
            'criminal_record_declaration', 'driving_violation_declaration', 
            'clean_driving_record_declaration', 'weekend_availability'
        ];
        
        // Only override checkboxes if we are submitting the tab that contains them.
        // For simplicity, we just set them to what the request has, since the whole form submits.
        foreach ($checkboxes as $cb) {
            if ($request->has($cb)) {
                $input[$cb] = true;
            } else {
                $input[$cb] = false;
            }
        }

        if ($user->role === User::ROLE_CHEF) {
            $profile = $user->chefProfile ?? new ChefProfile(['user_id' => $user->id]);
            $profile->fill($input);
            $profile->save();
        } elseif ($user->role === User::ROLE_TRAVELER) {
            $profile = $user->travelerProfile ?? new TravelerProfile(['user_id' => $user->id]);
            $profile->fill($input);
            $profile->save();
        }

        // If it's the final submission or user pressed Update and Final Submission
        // Assuming 'tos_agreement' and 'background_check_consent' is checked
        // This signifies the very last tab is filled.
        if ($request->has('tos_agreement') && $request->has('background_check_consent')) {
            if ($user->status !== User::STATUS_APPROVED) {
                $user->status = User::STATUS_PENDING;
                $user->save();
            }
            return redirect()->route('dashboard')->with('success', 'Your verification details have been submitted for review.');
        }

        return back()->with('success', 'Progress saved.')->with('active_tab', $request->input('active_tab', '#tab-identity'));
    }
}
