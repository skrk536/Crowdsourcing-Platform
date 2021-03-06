<?php


namespace App\BusinessLogicLayer;


use App\BusinessLogicLayer\gamification\PlatformWideGamificationBadgesProvider;
use App\BusinessLogicLayer\gamification\QuestionnaireBadgeProvider;
use App\BusinessLogicLayer\questionnaire\QuestionnaireAccessManager;
use App\BusinessLogicLayer\questionnaire\QuestionnaireGoalManager;
use App\Models\ViewModels\GamificationNextStep;
use App\Models\ViewModels\QuestionnaireSocialShareButtons;
use App\Models\ViewModels\UserDashboardViewModel;
use App\Repository\CrowdSourcingProjectRepository;
use App\Repository\Questionnaire\QuestionnaireRepository;
use App\Repository\Questionnaire\Responses\QuestionnaireResponseRepository;

class UserDashboardManager {

    protected $projectRepository;
    protected $questionnaireRepository;
    protected $currentQuestionnaireCalculator;
    protected $platformWideGamificationBadgesProvider;
    protected $crowdSourcingProjectGoalManager;
    protected $questionnaireBadgeProvider;
    protected $questionnaireResponseRepository;
    protected $questionnaireAccessManager;

    public function __construct(CrowdSourcingProjectRepository $projectRepository,
                                QuestionnaireRepository $questionnaireRepository,
                                CurrentQuestionnaireProvider $currentQuestionnaireCalculator,
                                PlatformWideGamificationBadgesProvider $platformWideGamificationBadgesProvider,
                                QuestionnaireGoalManager $crowdSourcingProjectGoalManager,
                                QuestionnaireBadgeProvider $questionnaireBadgeProvider,
                                QuestionnaireResponseRepository $questionnaireResponseRepository,
                                QuestionnaireAccessManager $questionnaireAccessManager) {
        $this->projectRepository = $projectRepository;
        $this->questionnaireRepository = $questionnaireRepository;
        $this->currentQuestionnaireCalculator = $currentQuestionnaireCalculator;
        $this->platformWideGamificationBadgesProvider = $platformWideGamificationBadgesProvider;
        $this->crowdSourcingProjectGoalManager = $crowdSourcingProjectGoalManager;
        $this->questionnaireBadgeProvider = $questionnaireBadgeProvider;
        $this->questionnaireResponseRepository = $questionnaireResponseRepository;
        $this->questionnaireAccessManager = $questionnaireAccessManager;
    }


    public function getUserDashboardViewModel($user): UserDashboardViewModel {
        // should show only projects that are published and have at least 1 published questionnaire
        $projects = $this->projectRepository->getActiveProjectsWithAtLeastOneActiveQuestionnaire();

        foreach ($projects as $project) {
            // for each project, get the suitable questionnaire for the user
            $project->currentQuestionnaireForUser = $this->currentQuestionnaireCalculator
                ->getCurrentQuestionnaire($project->id, $user->id);
            $project->currentQuestionnaireGoalVM = $this->crowdSourcingProjectGoalManager
                ->getQuestionnaireGoalViewModel($project->currentQuestionnaireForUser);
            $nextUnlockableBadgeForCurrentQuestionnaire = $this->questionnaireBadgeProvider
                ->getNextUnlockableBadgeToShowForQuestionnaire($project->currentQuestionnaireForUser, $user->id);
            $project->userHasAccessToViewCurrentQuestionnaireStatisticsPage = $this->questionnaireAccessManager
                ->userHasAccessToViewQuestionnaireStatisticsPage($user, $project->currentQuestionnaireForUser);
            $project->gamificationNextStepVM = new GamificationNextStep(
                $project,
                $nextUnlockableBadgeForCurrentQuestionnaire->getNextStepMessage(),
                $nextUnlockableBadgeForCurrentQuestionnaire->imageFileName,
                true,
                new QuestionnaireSocialShareButtons($project, $project->currentQuestionnaireForUser, $user->id),
                $this->questionnaireResponseRepository->questionnaireResponseExists($project->currentQuestionnaireForUser->id, $user->id));
        }
        $platformWideGamificationBadgesVM = $this->platformWideGamificationBadgesProvider->getPlatformWideGamificationBadgesListVM($user->id);

        return new UserDashboardViewModel($projects, $platformWideGamificationBadgesVM);
    }

}
