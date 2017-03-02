<?php
require './vendor/autoload.php';

use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\StateMachine;

class PullRequest {

    private $marking = 'ready';

    public function getMarking()
    {
        return $this->marking;
    }
    public function setMarking($marking)
    {
        $this->marking = $marking;
    }
}

$builder = new DefinitionBuilder();
$builder->addPlaces(['ready', 'wip', 'in-review', 'merged']);

$builder->addTransition(new Transition('start-working', 'ready', 'wip'));
$builder->addTransition(new Transition('request-review', 'wip', 'in-review'));
$builder->addTransition(new Transition('feedback', 'in-review', 'wip'));
$builder->addTransition(new Transition('merge', 'in-review', 'merged'));
$definition = $builder->build();

$workflow = new StateMachine($definition);

$pr = new PullRequest();

$response = $workflow->can($pr, 'hoge'); // False
var_dump($response);

$response = $workflow->can($pr, 'merge'); // False
var_dump($response);

$response = $workflow->apply($pr, 'merge');
//var_dump($response);

$response = $workflow->can($pr, 'start-working'); // True
var_dump($response);

$response = $workflow->apply($pr, 'start-working');
var_dump($response);

$response = $workflow->can($pr, 'request-review'); // True
var_dump($response);

$response = $workflow->getEnabledTransitions($pr); // ['feedback', 'merge']
var_dump($response);
