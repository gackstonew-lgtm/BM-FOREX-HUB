<?php
/**
 * BM Forex Hub — Knowledge Base Service Layer
 */

require_once __DIR__ . '/../Models/AIKnowledge.php';

class KnowledgeBaseService {

    /**
     * Search knowledge base for exact/partial keyword matches
     */
    public static function searchKnowledge($query) {
        $published = AIKnowledge::getAll(null, true);
        $cleanQuery = strtolower(trim($query));
        
        $words = array_filter(explode(' ', preg_replace('/[^\w\s]/', '', $cleanQuery)));
        
        $bestMatch = null;
        $highestScore = 0;

        foreach ($published as $entry) {
            $score = 0;
            $question = strtolower($entry['question']);
            $answer   = strtolower($entry['answer']);
            $keywords = strtolower($entry['keywords']);

            // Direct question match
            if (strpos($cleanQuery, $question) !== false || strpos($question, $cleanQuery) !== false) {
                $score += 50;
            }

            // Keyword match
            if (!empty($keywords)) {
                $kwList = explode(',', $keywords);
                foreach ($kwList as $kw) {
                    $kw = trim($kw);
                    if (!empty($kw) && strpos($cleanQuery, $kw) !== false) {
                        $score += 35;
                    }
                }
            }

            // Word-level match
            foreach ($words as $word) {
                if (strlen($word) < 3) continue;
                if (strpos($question, $word) !== false) $score += 5;
                if (strpos($keywords, $word) !== false) $score += 8;
                if (strpos($answer, $word) !== false) $score += 2;
            }

            if ($score > $highestScore && $score >= 12) {
                $highestScore = $score;
                $bestMatch = $entry;
            }
        }

        return $bestMatch;
    }
}
