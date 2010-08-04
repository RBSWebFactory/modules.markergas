<?php
class markergas_BlockEcommercetrackingAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 *
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}
		$cartInfo = order_CartService::getInstance()->getDocumentInstanceFromSession();
		if ($cartInfo !== null)
		{
			$order = $cartInfo->getOrder();
			if ($order instanceof order_persistentdocument_order)
			{	
				$bills = order_BillService::getInstance()->getByOrder($order);
				$bill = count($bills) == 0 ? null : f_util_ArrayUtils::firstElement($bills);
				if ($this->getConfiguration()->getTrackonlypaidorders() ? ($bill && $bill->getStatus() !== order_BillService::FAILED) : true)
				{
					$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
					$lang = RequestContext::getInstance()->getLang();
					$markers = website_MarkerService::getInstance()->getByWebsiteAndLang($website, $lang);
					foreach ( $markers as $marker )
					{
						if ($marker instanceof markergas_persistentdocument_markergas && $marker->getUseEcommerce() && in_array($order->getBillingModeId(), DocumentHelper::getIdArrayFromDocumentArray($marker->getBillingmodesArray())))
						{
							$html = markergas_MarkergasService::getInstance()->getEcommercePlainMarker($order, $marker, $this->getConfiguration()->getIncludetaxes());
							$this->getContext()->appendToPlainMarker($html);
							break;
						}
					}
				}
			}
		}
		return website_BlockView::NONE;
	}
}