<?php
/**
 * DokuWiki Plugin illwikishorthand (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Tim <tim@gurka.se>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_illwikishorthand extends DokuWiki_Syntax_Plugin
{
    private static $shorthand_img = array(
        '{{order}}' => [ ':misc:scales:order.png', 'Order scales' ],
        '{{turmoil}}' => [ ':misc:scales:turmoil.png', 'Turmoil scales' ],
        '{{productivity}}' => [ ':misc:scales:productivity.png', 'Productivity scales' ],
        '{{sloth}}' => [ ':misc:scales:sloth.png', 'Sloth scales' ],
        '{{heat}}' => [ ':misc:scales:heat.png', 'Heat scales' ],
        '{{cold}}' => [ ':misc:scales:cold.png', 'Cold scales' ],
        '{{growth}}' => [ ':misc:scales:growth.png', 'Growth scales' ],
        '{{death}}' => [ ':misc:scales:death.png', 'Death scales' ],
        '{{luck}}' => [ ':misc:scales:luck.png', 'Luck scales' ],
        '{{misfortune}}' => [ ':misc:scales:misfortune.png', 'Misfortune scales' ],
        '{{magic}}' => [ ':misc:scales:magic.png', 'Magic scales' ],
	'{{drain}}' => [ ':misc:scales:drain.png', 'Drain scales' ],
        '{{gold}}' => [ ':misc:gui:gold.png', 'Gold' ],
        '{{resources}}' => [ ':misc:gui:resources.png', 'Resources' ],
        '{{recpoints}}' => [ ':misc:gui:recruitmentpoints.png', 'Recruitment points' ]
    );

    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'normal';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 300;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('{{path>.+?}}', $mode, 'plugin_illwikishorthand');
        $this->Lexer->addSpecialPattern('{{gems>(?:\\d*(?:\\.\\d+)?[FAWESNDGB])+}}', $mode, 'plugin_illwikishorthand');
        $this->Lexer->addSpecialPattern('{{scales>(?:[a-zA-Z0-9])+}}', $mode, 'plugin_illwikishorthand');
	$img_regex = implode('|',array_keys(self::$shorthand_img));
        $this->Lexer->addSpecialPattern($img_regex, $mode, 'plugin_illwikishorthand');
    }

//    public function postConnect()
//    {
//        $this->Lexer->addExitPattern('</FIXME>', 'plugin_illwikishorthand');
//    }

    /**
     * Handle matches of the illwikishorthand syntax
     *
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     *
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
	if ( preg_match( '/^{{(path|gems|scales)>(.*?)}}$/', $match, $m ) ) {
		$shorthand = $m[1];
		$str = $m[2];
		$data = array( $shorthand, $str );
		return $data;
	} elseif ( array_key_exists( $match, self::$shorthand_img ) ) {
		return [ 'img', self::$shorthand_img[$match][0], self::$shorthand_img[$match][1] ];
	} else {
		return;
	}
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string        $mode     Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array         $data     The data from the handler() function
     *
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            return false;
        }

	$abbrv = array('F'=>'fire','A'=>'air','W'=>'water','E'=>'earth','D'=>'death','S'=>'astral','N'=>'nature','G'=>'glamour','B'=>'blood','H'=>'holy','R'=>'rp');
	if ( $data[0] == 'img' ) {
		$renderer->internalmedia(
			$data[1],
			$data[2],
			NULL, NULL, NULL, NULL, 'nolink'
		);
	} elseif ( $data[0] == 'scales' ) {
		$renderer->doc .= "<span class=\"illwikishorthand_scales\">";
		# dom str
		if ( preg_match( '/^Dom([0-9][0-9]?)/i', $data[1], $m ) ) {
			$level = $m[1];
			$data[1] = preg_replace( '/^Dom[0-9][0-9]?/i', '', $data[1], 1 );
			$renderer->internalmedia( ':misc:dominioncandle.png', 'Dominion strength', NULL, 4, NULL, NULL, 'nolink' );
			$renderer->doc .= $level;
		}
		# scales
		$scales_abbrv = array(
			'O'=>'order',
			'OR'=>'order',
			'T'=>'turmoil',
			'TU'=>'turmoil',
			'P'=>'productivity',
			'PR'=>'productivity',
			'S'=>'sloth',
			'SL'=>'sloth',
			'H'=>'heat',
			'HE'=>'heat',
			'C'=>'cold',
			'CO'=>'cold',
			'G'=>'growth',
			'GR'=>'growth',
			'L'=>'luck',
			'LU'=>'luck',
			'M'=>'magic', # ambiguous; somewhat disambiguated by position
			'MA'=>'magic',
			'MF'=>'misfortune',
			'MI'=>'misfortune',
			'D'=>'drain', # ambiguous; somewhat disambiguated by position
			'DR'=>'drain',
			'DE'=>'death'
		);
		preg_match_all( '/([A-Za-z]{1,2})([0-3])/', $data[1], $m );
		for ( $i = 0; $i < count($m[0]); $i++ ) {
			$scale = strtoupper( $m[1][$i] );
			$level = $m[2][$i];
			if ( array_key_exists( $scale, $scales_abbrv ) ) {
				if ( $scale == 'M' && $i == 4 ) {
					$scale_name = 'misfortune';
				} elseif ( $scale == 'M' ) {
					$scale_name = 'magic';
				} elseif ( $scale == 'D' && $i == 3 ) {
					$scale_name = 'death';
				} elseif ( $scale == 'D' ) {
					$scale_name = 'drain';
				} else {
					$scale_name = $scales_abbrv[$scale];
				}
				$renderer->internalmedia(
					':misc:scales:'.$scale_name.'.png',
					ucfirst($scale_name),
					NULL, 14, NULL, NULL, 'nolink'
				);
				$renderer->doc .= $level;
				#$renderer->doc .= "[$scale $level]";
			} else {
				$renderer->doc .= "[$scale$level]";
			}
		}
		$renderer->doc .= '</span>';
	} elseif ( $data[0] == 'path' ) {
		$renderer->doc .= "<span class=\"illwikishorthand_paths\">";
		$random_tot = 0;
		$random_tooltip_strs = array();
		$path_parts = explode(',', $data[1] );
		foreach ( $path_parts as $path_part ) {
			if ( preg_match( '/^(?:[FAWEDSNGBHR]\\d+)+$/', $path_part, $m ) ) {
				// Base path
				preg_match_all( '/([FAWEDSNGBHR])(\d+)/', $path_part, $m );
				for ( $i = 0; $i < count($m[0]); $i++ ) {
					$path = $m[1][$i];
					$level = $m[2][$i];
					$renderer->internalmedia(
						':misc:magic:'.$abbrv[$path].'.png',
						$abbrv[$path] . ' ' . $level,
						NULL, 14, NULL, NULL, 'nolink'
					);
					$renderer->doc .= $level;
				}
			} elseif ( preg_match( '/^(\\d+)%([FAWEDSNGBH]+)$/', $path_part, $m ) ) {
				// Random path
				$chance = $m[1];
				$paths = str_split($m[2]);
				$random_tot += $chance;
				$tooltip_str = "$chance%";
				foreach ( $paths as $path ) {
					$tooltip_str .= $renderer->internalmedia(
						':misc:magic:'.$abbrv[$path].'.png',
						'', NULL, 12, NULL, NULL, 'nolink', true
					);
				}
				$random_tooltip_strs[] = $tooltip_str;
			}
		}
		if ( $random_tot > 0 ) {
			$renderer->doc .= '<span class="illwikishorthand_path_random">';
			$renderer->internalmedia( ':misc:magic:random.png', 'random', NULL, 8, NULL, NULL, 'nolink');
			$renderer->doc .= '<span class="illwikishorthand_path_summary">';
			$renderer->doc .= round($random_tot/100);
			$renderer->doc .= "</span>";
			$renderer->doc .= sprintf( '<span class="illwikishorthand_path_tooltip">%s</span>', implode('<br/>', $random_tooltip_strs ) );
			$renderer->doc .= "</span>";
		}
		$renderer->doc .= "</span>";
	} elseif ( $data[0] == 'gems' ) {
		$gem_abbrv = array('F'=>'firegem','A'=>'airgem','W'=>'watergem','E'=>'earthgem','D'=>'deathgem','S'=>'astralpearl','N'=>'naturegem','G'=>'glamourgem','B'=>'bloodslave');
		preg_match_all( '/(\\d*(?:\\.\\d+)?)([FAWEDSNGB])/', $data[1], $m );
		$renderer->doc .= '<span class="illwikishorthand_gems">';
		for ( $i = 0; $i < count($m[0]); $i++ ) {
			$num = $m[1][$i];
			$gemtype = $m[2][$i];
			if ( $num ) {
				$renderer->doc .= $num;
			}
			$renderer->internalmedia(
				':misc:magic:'.$gem_abbrv[$gemtype].'.png',
				$gem_abbrv[$gemtype],
				NULL, NULL, NULL, NULL, 'nolink'
			);
		}
		$renderer->doc .= '</path>';
	} else {
		$renderer->doc .= '['.$data[0].']';
	}

        return true;
    }
}

