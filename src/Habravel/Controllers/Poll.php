<?php namespace Habravel\Controllers;

use Habravel\Models\PollModel as PollModelModel;
use Habravel\Models\PollVoteModel as PollVoteModelModel;

class Poll extends BaseController {
  static function voteOn(Models\BaseModel $model, $up) {
    $model or App::abort(404);
    $model->poll or App::abort(403, 'This '.class_basename($model).' cannot be voted for.');
    $response = static::saveVotes(array(array('poll' => $model->poll, 'option' => $up + 1)));

    $votes = PollVoteModel
      ::wherePoll($model->poll)
      ->whereIn('option', array(1, 2))
      ->groupBy('option')
      ->lists(\DB::raw('COUNT(1)'), 'option');

    $model->score = array_get($votes, 2, 0) - array_get($votes, 1, 0);
    $model->save();

    return $response;
  }

  // $votes = array(array('option' => id/null, 'poll' => id), ...).
  // Warning: IDs are not verified and thus must exist and belong to the polls.
  protected static function saveVotes(array $votes) {
    if (!user()) {
      App::abort(401);
    } elseif (!user()->hasFlag('can.vote')) {
      App::abort(403);
    }

    $votes = static::normalizeVotes($votes);

    if ($votes) {
      $records = array();
      $user = user()->id;
      $ip = Request::getClientIp();

      PollVoteModel
        ::whereIn('poll', array_pluck($votes, 'poll'))
        ->whereUser($user)
        ->delete();

      foreach ($votes as $vote) {
        $records[] = array_only($vote, array('poll', 'option')) + compact('user', 'ip');
      }

      \DB::table('poll_votes')->insert($records);
    }

    $url = \Habravel\referer(\URL::previous());
    strrchr($url, '#') or $url .= '#polls';
    return Redirect::to($url);
  }

  protected static function normalizeVotes(array $votes) {
    if (!$votes) { return array(); }

    $multiple = PollModel
      ::whereMultiple(1)
      ->whereIn('id', array_pluck($votes, 'poll'))
      ->lists('id', 'id');

    $norm = array();

    foreach ($votes as $vote) {
      if (!$vote['option']) {   // abstained from vote.
        unset($multiple[$vote['poll']]);
        $norm[] = $vote;
      }
    }

    // Remove multiple voted options for single-option polls.
    foreach ($votes as $vote) {
      if (!isset($multiple[$vote['poll']])) {
        foreach ($norm as $normVote) {
          if ($normVote['poll'] === $vote['poll']) {
            $vote = null;
            break;
          }
        }
      }

      $vote and $norm[] = $vote;
    }

    return $norm;
  }

  function __construct() {
    parent::__construct();
    $this->beforeFilter('csrf', array('only' => array('vote')));
  }

  // POST input:
  // - votes[]=optionID   - adds user's vote for given option.
  // - votes[]=-pollID    - abstain.
  function vote() {
    $votes = array();

    if ($input = Input::get('votes')) {
      $abstain = [];
      foreach ($input as $id) {
        $id[0] === '-' and $abstain[] = (int) substr($id, 1);
      }

      $polls = PollModel
        ::join('poll_options', 'poll_options.poll', '=', 'polls.id')
        ->whereNull('poll_options.deleted_at')
        ->whereNull('polls.deleted_at')
        ->whereIn('poll_options.id', $input)
        ->get(array('polls.*', 'poll_options.id AS optionID'));

      foreach ($polls as $poll) {
        $votes[] = array(
          'poll'            => $poll->id,
          'option'          => $poll->optionID,
        );
      }

      if ($abstain) {
        $abstain = PollModel::whereNull('deleted_at')->whereIn('id', $abstain)->lists('id');

        foreach ($abstain as $poll) {
          $votes[] = array('poll' => $poll, 'option' => null);
        }
      }
    }

    return static::saveVotes($votes);
  }
}