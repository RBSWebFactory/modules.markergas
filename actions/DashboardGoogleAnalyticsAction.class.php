<?php
class markergas_DashboardGoogleAnalyticsAction extends dashboard_BaseModuleAction
{
	/**
	 * @see dashboard_BaseModuleAction::getIcon()
	 *
	 * @return string
	 */
	protected function getIcon()
	{
		return 'line-chart';
	}
	
	/**
	 * @see dashboard_BaseModuleAction::getTitle()
	 *
	 * @return string
	 */
	protected function getTitle()
	{
		return LocaleService::getInstance()->trans('m.markergas.bo.dashboard.report-block', array('ucf'));
	}
	
	/**
	 * @see dashboard_BaseModuleAction::getContent()
	 *
	 * @param change_Context $context
	 * @param change_Request $request
	 * @return string
	 */
	protected function getContent($context, $request)
	{
		$report = $request->getParameter('report', 'VisitsReport');
		$markerId = $request->getParameter('markerId');
		try 
		{
			$marker = DocumentHelper::getDocumentInstance($markerId);
			$mgs = markergas_MarkergasService::getInstance();
			$websitesAndLangs = $mgs->getRelatedWebsitesAndLangsByMarkergas($marker);
			$websitesLabel = $mgs->getLabelFromWebsitesAndLangs($websitesAndLangs);
		}
		catch (Exception $e)
		{
			Framework::exception($e);
			return LocaleService::getInstance()->trans('m.markergas.bo.dashboard.error-invalid-marker', array('ucf'));
		}
		
		if ($marker->getLogin() && $marker->getPassword() && $marker->getGaSiteId())
		{
			$reader = new markergas_GoogleAnalyticsReader($marker->getLogin(), $marker->getPassword(), $marker->getGaSiteId(), 'fr_FR');
			$xmlData = $reader->queryAsXml($report);
			$reader->close();
			
			$widget = markergas_GoogleAnalyticsService::getInstance()->parseXmlReport($xmlData);
			if ($widget !== null)
			{
				$templateObject = $this->createNewTemplate('modules_markergas', 'Markergas-Action-DashboardGoogleAnalytics', 'html');
				$templateObject->setAttribute('websitesLabel', $websitesLabel);
				$templateObject->setAttribute('widget', $widget);
				$templateObject->setAttribute('report', $report);
				$templateObject->setAttribute('markerId', $markerId);
				return $templateObject->execute();
			}
			else
			{
				return LocaleService::getInstance()->trans('m.markergas.bo.dashboard.error-getting-data', array('ucf'));
			}
		}
		else
		{
			return LocaleService::getInstance()->trans('m.markergas.bo.dashboard.error-invalid-marker', array('ucf'));
		}
	}
}