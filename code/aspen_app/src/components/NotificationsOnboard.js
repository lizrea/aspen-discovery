import { useFocusEffect } from '@react-navigation/native';
import { useQueryClient } from '@tanstack/react-query';
import { AlertDialog, Button } from 'native-base';
import React from 'react';
import { LanguageContext, LibrarySystemContext, UserContext } from '../context/initialContext';
import { navigateStack } from '../helpers/RootNavigator';
import { getTermFromDictionary } from '../translations/TranslationService';
import { refreshProfile, updateNotificationOnboardingStatus } from '../util/api/user';

export const NotificationsOnboard = (props) => {
     const queryClient = useQueryClient();
     const { setAlreadyCheckedNotifications, setShowNotificationsOnboarding } = props;
     const { language } = React.useContext(LanguageContext);
     const { library } = React.useContext(LibrarySystemContext);
     const { user, notificationSettings, expoToken, notificationOnboard, updateNotificationOnboard, updateNotificationSettings } = React.useContext(UserContext);
     const [isOpen, setIsOpen] = React.useState(true);
     const [onboardingBody, setOnboardingBody] = React.useState('');
     const [onboardingButton, setOnboardingButton] = React.useState('');
     const [isLoading, setIsLoading] = React.useState(false);
     const [isCanceling, setIsCanceling] = React.useState(false);
     const onClose = async () => {
          await updateNotificationOnboardingStatus(false, expoToken, library.baseUrl, language);
          await refreshProfile(library.baseUrl).then((profile) => {
               updateNotificationSettings(profile.notification_preferences, language, false);
               setIsOpen(false);
          });

          updateNotificationOnboard(0);
          setIsLoading(false);
          setIsCanceling(false);
          //setAlreadyCheckedNotifications(true);
          //setShowNotificationsOnboarding(false);
     };

     const cancelRef = React.useRef(null);

     useFocusEffect(
          React.useCallback(() => {
               const getTranslations = async () => {
                    if (notificationOnboard === 2 || notificationOnboard === '2') {
                         setOnboardingBody(getTermFromDictionary(language, 'onboard_notifications_body_update'));
                         setOnboardingButton(getTermFromDictionary(language, 'onboard_notifications_button_update'));
                    } else if (notificationOnboard === 1 || notificationOnboard === '1') {
                         setOnboardingBody(getTermFromDictionary(language, 'onboard_notifications_body_new'));
                         setOnboardingButton(getTermFromDictionary(language, 'onboard_notifications_button_new'));
                    } else {
                         setIsOpen(false);
                         //setAlreadyCheckedNotifications(true);
                         updateNotificationOnboard(0);
                         try {
                              await updateNotificationOnboardingStatus(false, expoToken, library.baseUrl, language);
                         } catch (e) {
                              // onboarding isn't setup yet (Discovery older than 23.07.00)
                         }
                         await refreshProfile(library.baseUrl).then((profile) => {
                              updateNotificationSettings(profile.notification_preferences, language, false);
                         });
                    }
               };
               getTranslations().then(() => {
                    return () => getTranslations();
               });
          }, [])
     );

     return (
          <AlertDialog leastDestructiveRef={cancelRef} isOpen={isOpen} onClose={() => onClose()}>
               <AlertDialog.Content>
                    <AlertDialog.Header>{getTermFromDictionary(language, 'onboard_notifications_title')}</AlertDialog.Header>
                    <AlertDialog.Body>{onboardingBody}</AlertDialog.Body>
                    <AlertDialog.Footer>
                         <Button.Group space={2}>
                              <Button
                                   isLoading={isCanceling}
                                   isLoadingText={getTermFromDictionary(language, 'canceling', true)}
                                   variant="unstyled"
                                   colorScheme="coolGray"
                                   onPress={() => {
                                        setIsCanceling(true);
                                        onClose();
                                   }}
                                   ref={cancelRef}>
                                   {getTermFromDictionary(language, 'onboard_notifications_button_cancel')}
                              </Button>
                              <Button
                                   isLoading={isLoading}
                                   isLoadingText={getTermFromDictionary(language, 'updating', true)}
                                   colorScheme="danger"
                                   onPress={() => {
                                        setIsLoading(true);
                                        onClose().then(() => navigateStack('MoreTab', 'PermissionNotificationDescription', { prevRoute: 'notifications_onboard' }));
                                   }}>
                                   {onboardingButton}
                              </Button>
                         </Button.Group>
                    </AlertDialog.Footer>
               </AlertDialog.Content>
          </AlertDialog>
     );
};