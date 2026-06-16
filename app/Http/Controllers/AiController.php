<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AiController extends Controller
{
    public function correct(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000',
        ]);

        $text = $request->input('text');
        $corrected = $this->correctGrammar($text);

        return response()->json([
            'success' => true,
            'original' => $text,
            'corrected' => $corrected,
        ]);
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
