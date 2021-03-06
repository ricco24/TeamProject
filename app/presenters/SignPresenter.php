<?php        

/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class SignPresenter extends BasePresenter
{          
	
	public function startup() {
		parent::startup();
		
		$this->setLayout('sign');
	}

	/**
	 * Sign in form component factory.
	 * @return AppForm
	 */
	protected function createComponentSignInForm()
	{
		$form = new NAppForm;
		$form->addText('username', 'Username:')
			->setRequired('Please provide a username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please provide a password.');

		$form->addCheckbox('remember', 'Remember me on this computer');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}



	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');

		} catch (NAuthenticationException $e) {
			$this->flashMessage($e->getMessage(), 'error');
			$this->redirect('Sign:in');
		}
	}



	public function actionOut()
	{
		$this->user->logout($this->user->identity);
		$this->flashMessage('You have been signed out.');
		$this->redirect('homepage:default');
	}

}
