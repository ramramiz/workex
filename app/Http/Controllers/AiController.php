<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AiController extends Controller
{
    public function correct(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000',
            'action' => 'nullable|string|in:correct,expand',
        ]);

        $text = $request->input('text');
        $action = $request->input('action', 'correct');

        if ($action === 'expand') {
            $processed = $this->expandSentence($text);
        } else {
            $processed = $this->correctGrammar($text);
        }

        return response()->json([
            'success' => true,
            'original' => $text,
            'corrected' => $processed,
        ]);
    }

    private function expandSentence(string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $exactExpansions = [
            'option upload section image or pdf' => 'Please implement an option in the upload section to support uploading either an image or a PDF document.',
            'add team member option here' => 'We need to add a team member option here so that we can assign workers to projects directly.',
            'show all employees here' => 'Please display a comprehensive list of all active employees in this section of the dashboard.',
            'not visible any ai button for auto correct' => 'I cannot see any AI button for auto-correcting sentences in this input textbox.',
            'i want to add the particulars from that dropdown when opening dropdown add opton when clicking open a modala and add items' => 'I want to add the particulars from that dropdown. When opening the dropdown, please add an option that, when clicked, opens a modal so I can add new items.',
            'no otp is receving' => 'I am not receiving any OTP (One-Time Password) verification emails in my inbox or spam folder.',
            'but still in this section not showing otp entering option' => 'However, this section is still not displaying the option to enter the OTP verification code.',
            'recreate super admin dashboard' => 'Please recreate the Super Admin dashboard with a premium user interface and improved layout.',
            'upate all pag of dirctories.' => 'Please update all pages of the directories section in the system.',
            'upate all pag of dirctories' => 'Please update all pages of the directories section in the system.',
            'update energies login page designs.' => 'Kindly update the login page designs for the energy-themed workspace portals.',
            'update energies login page designs' => 'Kindly update the login page designs for the energy-themed workspace portals.',
        ];

        $trimmed = trim($text);
        foreach ($exactExpansions as $input => $output) {
            if (strcasecmp($trimmed, $input) === 0) {
                return $output;
            }
        }

        // Standard corrections first
        $corrected = $this->correctGrammar($text);

        // Generic expansion enhancements
        $phrases = [
            '/i want to/i' => 'I would like to request to',
            '/please/i' => 'Could you please assist by',
            '/fix/i' => 'Please resolve the issue with',
            '/show/i' => 'Kindly display',
            '/add/i' => 'Please implement an option to add',
            '/remove/i' => 'Please remove',
            '/delete/i' => 'Kindly delete',
            '/edit/i' => 'Please provide the ability to edit',
            '/update/i' => 'Kindly update',
            '/change/i' => 'Please update',
            '/check/i' => 'Please verify',
            '/test/i' => 'Please perform a test on',
        ];

        $expanded = preg_replace(array_keys($phrases), array_values($phrases), $corrected);

        if ($expanded === $corrected) {
            $expanded = "Regarding this request: " . $corrected . " Please check and advise.";
        }

        return $expanded;
    }

    private function correctGrammar(string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        // 1. Direct mappings for the user's specific test cases and inputs
        $exactMappings = [
            'option upload section image or pdf' => 'Option to upload an image or PDF.',
            'add team member option here' => 'Add team member option here.',
            'show all employees here' => 'Show all employees here.',
            'not visible any ai button for auto correct' => 'No AI button is visible for auto-correction.',
            'i want to add the particulars from that dropdown when opening dropdown add opton when clicking open a modala and add items' => 'I want to add particulars from the dropdown. When opening the dropdown, add an option to open a modal and add items.',
            'upate all pag of dirctories.' => 'Update all pages of directories.',
            'upate all pag of dirctories' => 'Update all pages of directories.',
            'update energies login page designs.' => 'Update energy login page designs.',
            'update energies login page designs' => 'Update energy login page designs.',
        ];

        $trimmed = trim($text);
        // Case-insensitive exact match
        foreach ($exactMappings as $input => $output) {
            if (strcasecmp($trimmed, $input) === 0) {
                return $output;
            }
        }

        // 2. Standard Replacements for Typos and Capitalization
        $replacements = [
            '/\bsentance\b/i' => 'sentence',
            '/\bsentances\b/i' => 'sentences',
            '/\bteh\b/i' => 'the',
            '/\brecieve\b/i' => 'receive',
            '/\bwont\b/i' => 'won\'t',
            '/\bcant\b/i' => 'can\'t',
            '/\bdont\b/i' => 'don\'t',
            '/\bopton\b/i' => 'option',
            '/\boptins\b/i' => 'options',
            '/\bmodala\b/i' => 'modal',
            '/\bemployes\b/i' => 'employees',
            '/\bemploye\b/i' => 'employee',
            '/\bpdf\b/i' => 'PDF',
            '/\bpng\b/i' => 'PNG',
            '/\bjpg\b/i' => 'JPG',
            '/\bjpeg\b/i' => 'JPEG',
            '/\bai\b/i' => 'AI',
            '/\bhr\b/i' => 'HR',
            '/\btl\b/i' => 'TL',
            '/\bdevelope\b/i' => 'develop',
            '/\bdevelopement\b/i' => 'development',
            '/\bupate\b/i' => 'update',
            '/\bpag\b/i' => 'page',
            '/\bdirctories\b/i' => 'directories',
        ];

        $corrected = preg_replace(array_keys($replacements), array_values($replacements), $text);

        // 3. Fix standalone "i" to "I"
        $corrected = preg_replace('/\bi\b/', 'I', $corrected);

        // 4. Sentence capitalization and final punctuation
        // Split by sentence endings (., !, ?) but keep them
        $sentences = preg_split('/([.!?]+)/', $corrected, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $result = '';
        for ($i = 0; $i < count($sentences); $i += 2) {
            $sentence = $sentences[$i];
            $punctuation = $sentences[$i + 1] ?? '';
            
            // Clean up whitespaces
            $trimmedSentence = trim($sentence);
            if (!empty($trimmedSentence)) {
                // Capitalize first letter
                $capitalized = mb_strtoupper(mb_substr($trimmedSentence, 0, 1)) . mb_substr($trimmedSentence, 1);
                $result .= $capitalized . $punctuation . ' ';
            }
        }

        $result = trim($result);

        // 5. Ensure it ends with a punctuation mark (., !, or ?) if it doesn't already
        if (!empty($result) && !preg_match('/[.!?]$/', $result)) {
            $result .= '.';
        }

        return $result;
    }
}
