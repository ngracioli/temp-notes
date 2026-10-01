<?php

return [

    /*
    | Keep CLAUDE.md as a single "@AGENTS.md" import so Claude Code does not
    | receive the guidelines twice. Other Boost options use package defaults.
    */

    'agents' => [
        'claude_code' => [
            'guidelines_path' => 'AGENTS.md',
        ],
    ],

];
