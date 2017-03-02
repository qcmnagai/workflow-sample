<?php
require './vendor/autoload.php';

use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class PullRequest {

    private $marking = ['ready' => 1, 'opened' => 1];

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
$builder->addPlaces(['opened', 'closed']);

$builder->addTransition(new Transition('start-working', 'ready', 'wip'));
$builder->addTransition(new Transition('request-review', 'wip', 'in-review'));
$builder->addTransition(new Transition('feedback', 'in-review', 'wip'));
$builder->addTransition(new Transition('merge', 'in-review', 'merged'));
$builder->addTransition(new Transition('close', 'opened', 'closed'));
$definition = $builder->build();

$workflow = new Workflow($definition);

$pr = new PullRequest();

assert(false === $workflow->can($pr, 'merge'));
assert(true === $workflow->can($pr, 'start-working'));

$workflow->apply($pr, 'start-working');

assert(true === $workflow->can($pr, 'request-review'));

$workflow->apply($pr, 'request-review');

assert(true === $workflow->can($pr, 'feedback'));

$workflow->apply($pr, 'feedback');
$workflow->apply($pr, 'request-review');

var_dump($workflow->getEnabledTransitions($pr));
assert(['feedback', 'merge', 'close'] == $workflow->getEnabledTransitions($pr));

var_dump($pr->getMarking());
