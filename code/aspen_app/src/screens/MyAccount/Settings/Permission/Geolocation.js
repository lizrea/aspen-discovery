import { ScrollView, AlertDialog, AlertDialogBackdrop, HStack, VStack, Pressable, Icon, Text, Center, Button, ButtonText, ButtonIcon, ButtonGroup, Heading, Box, Accordion, AlertDialogBody, AlertDialogContent, AlertDialogFooter, AlertDialogHeader, AccordionItem, AccordionContent, AccordionContentText, AccordionHeader, AccordionTrigger, AccordionTitleText, AccordionIcon } from '@gluestack-ui/themed';
import React from 'react';
import * as Location from 'expo-location';
import * as Linking from 'expo-linking';
import { Platform } from 'react-native';

import { useRoute } from '@react-navigation/native';
import { LanguageContext, ThemeContext } from '../../../../context/initialContext';
import { navigate } from '../../../../helpers/RootNavigator';
import { getTermFromDictionary } from '../../../../translations/TranslationService';
import { ChevronRight, ChevronUp, ChevronDown } from 'lucide-react-native';
import Constants from 'expo-constants';

export const GeolocationPermissionStatus = () => {
     const { language } = React.useContext(LanguageContext);
     const { colorMode, textColor } = React.useContext(ThemeContext);
     const [permissionStatus, setPermissionStatus] = React.useState(false);

     React.useEffect(() => {
          (async () => {
               const { status } = await Location.getForegroundPermissionsAsync();
               setPermissionStatus(status === 'granted');
          })();
     }, []);

     return (
          <Pressable onPress={() => navigate('PermissionGeolocationDescription', { permissionStatus })} pb="$3">
               <HStack space="md" justifyContent="space-between" alignItems="center">
                    <Text bold color={textColor}>
                         {getTermFromDictionary(language, 'geolocation_permission')}
                    </Text>
                    <HStack alignItems="center">
                         <Text color={textColor}>{permissionStatus === true ? getTermFromDictionary(language, 'allowed') : getTermFromDictionary(language, 'not_allowed')}</Text>
                         <Icon ml="$1" as={ChevronRight} color={textColor} />
                    </HStack>
               </HStack>
          </Pressable>
     );
};

export const GeolocationPermissionDescription = () => {
     const { colorMode, textColor } = React.useContext(ThemeContext);
     const permissionStatus = useRoute().params?.permissionStatus ?? false;
     const { language } = React.useContext(LanguageContext);

     return (
          <ScrollView p="$5">
               <VStack alignItems="stretch">
                    <Box>
                         <Text color={textColor}>{getTermFromDictionary(language, 'device_set_to')}</Text>

                         <Heading mb="$1" color={textColor}>
                              {permissionStatus === true ? getTermFromDictionary(language, 'allowed') : getTermFromDictionary(language, 'not_allowed')}
                         </Heading>
                         <Text color={textColor}>
                              {Constants.expoConfig.name} {permissionStatus === true ? getTermFromDictionary(language, 'allowed_geolocation') : getTermFromDictionary(language, 'not_allowed_geolocation')}
                         </Text>

                         <Text color={textColor} mt="$5">
                              {getTermFromDictionary(language, 'to_update_settings')}
                         </Text>
                         <GeolocationPermissionUsage />
                    </Box>
                    <GeolocationPermissionUpdate />
               </VStack>
          </ScrollView>
     );
};

const GeolocationPermissionUsage = () => {
     const { language } = React.useContext(LanguageContext);
     const { textColor } = React.useContext(ThemeContext);

     return (
          <Accordion variant="unfilled" w="100%" size="sm">
               <AccordionItem value="description">
                    <AccordionHeader>
                         <AccordionTrigger>
                              {({ isExpanded }) => {
                                   return (
                                        <>
                                             <AccordionTitleText color={textColor}>{getTermFromDictionary(language, 'how_we_use_geolocation_title')}</AccordionTitleText>
                                             {isExpanded ? <AccordionIcon as={ChevronUp} ml="$3" color={textColor} /> : <AccordionIcon as={ChevronDown} ml="$3" color={textColor} />}
                                        </>
                                   );
                              }}
                         </AccordionTrigger>
                    </AccordionHeader>
                    <AccordionContent>
                         <AccordionContentText color={textColor}>
                              {Constants.expoConfig.name} {getTermFromDictionary(language, 'how_we_use_geolocation_body')}
                         </AccordionContentText>
                    </AccordionContent>
               </AccordionItem>
          </Accordion>
     );
};

const GeolocationPermissionUpdate = () => {
     const { colorMode, theme, textColor } = React.useContext(ThemeContext);
     const { language } = React.useContext(LanguageContext);
     const [showAlertDialog, setShowAlertDialog] = React.useState(false);

     return (
          <Center>
               <Button onPress={() => setShowAlertDialog(true)} bgColor={theme['colors']['primary']['500']}>
                    <ButtonText color={theme['colors']['primary']['500-text']}>{getTermFromDictionary(language, 'update_device_settings')}</ButtonText>
               </Button>
               <AlertDialog
                    isOpen={showAlertDialog}
                    onClose={() => {
                         setShowAlertDialog(false);
                    }}>
                    <AlertDialogBackdrop />
                    <AlertDialogContent bgColor={colorMode === 'light' ? theme['colors']['warmGray']['50'] : theme['colors']['coolGray']['700']}>
                         <AlertDialogHeader>
                              <Heading color={textColor}>{getTermFromDictionary(language, 'update_device_settings')}</Heading>
                         </AlertDialogHeader>
                         <AlertDialogBody>
                              <Text color={textColor}>{Platform.OS === 'android' ? getTermFromDictionary(language, 'update_geolocation_android') : getTermFromDictionary(language, 'update_geolocation_ios')}</Text>
                         </AlertDialogBody>
                         <AlertDialogFooter>
                              <ButtonGroup flexDirection="column" alignItems="stretch" w="100%">
                                   <Button
                                        onPress={() => {
                                             Linking.openSettings();
                                             setShowAlertDialog(false);
                                        }}
                                        bgColor={theme['colors']['primary']['500']}>
                                        <ButtonText color={theme['colors']['primary']['500-text']}>{getTermFromDictionary(language, 'open_device_settings')}</ButtonText>
                                   </Button>
                                   <Button variant="link">
                                        <ButtonText color={textColor}>{getTermFromDictionary(language, 'not_now')}</ButtonText>
                                   </Button>
                              </ButtonGroup>
                         </AlertDialogFooter>
                    </AlertDialogContent>
               </AlertDialog>
          </Center>
     );
};