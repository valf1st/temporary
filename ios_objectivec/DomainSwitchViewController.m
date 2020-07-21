//
//  DomainSwitchViewController.m
//  KidsDiary
//
//  Created by FukudaAkali on 29/05/2019.
//  Copyright © 2019 **********. All rights reserved.
//

#import "DomainSwitchViewController.h"
#import "PopupPickerView.h"
#import "HyLoglnButton.h"
#import "HyTransitions.h"
#import "AppDelegate.h"
#import "RootViewController.h"
#import "KINWebBrowserViewController.h"

@interface DomainSwitchViewController ()
@property HyLoglnButton *hyLoglnButton;
@end

@implementation DomainSwitchViewController

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    _bgView.layer.cornerRadius = 24;
    _bgView.layer.masksToBounds = YES;
    [self.navigationController setNavigationBarHidden:YES animated:NO];
    
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    _backArrowIcon.image = [_backArrowIcon.image changeImageColor:[DeviceInfo checkIsIpad]? [UIColor darkGrayColor]:[UIColor darkGrayColor]];
    _downArrowIcon.image = [_downArrowIcon.image changeImageColor:[DeviceInfo checkIsIpad]? [UIColor darkGrayColor]:[UIColor darkGrayColor]];

    _userTextField.placeholder = NSLocalizedString(@"ユーザー名", nil);
    _passTextField.placeholder = NSLocalizedString(@"パスワード", nil);
    _linkTextField.placeholder = NSLocalizedString(@"チャンネル", nil);
    [_backBtn setTitle:NSLocalizedString(@"通常ログイン", nil) forState:UIControlStateNormal];
    [_forgetBtn setTitle:NSLocalizedString(@"暗証番号忘れ", nil) forState:UIControlStateNormal];
    [self createLoginBtn];
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (void)createLoginBtn{
    
    _hyLoglnButton = [[HyLoglnButton alloc] initWithFrame:[DeviceInfo checkIsIpad] ?
                      CGRectMake(0, 0, 320, 45)
                                                         :
                      CGRectMake(20,
                                 0,
                                 [UIScreen mainScreen].bounds.size.width - 60,
                                 45)];
    [_hyLoglnButton setBackgroundColor:BASE_BLUE];
    [_hyLoglnButton setTitleColor:[UIColor whiteColor] forState:UIControlStateNormal];
    [_hyLoglnButton setTitle:NSLocalizedString(@"ログイン", nil) forState:UIControlStateNormal];
    _hyLoglnButton.titleLabel.font = [UIFont boldSystemFontOfSize:20];
    [_hyLoglnButton addTarget:self action:@selector(signInOnClick:) forControlEvents:UIControlEventTouchUpInside];
    
    [_loginBtnView addSubview:_hyLoglnButton];
}


#pragma mark - UITextField
- (void)textFieldDidBeginEditing:(UITextField *)textField{
    if (![DeviceInfo checkIsIpad]) {
        [self.view scrollToY: -140];
    }
}

-(void) textFieldDidEndEditing:(UITextField *)textField{
    if (textField == _userTextField) {
        [_passTextField becomeFirstResponder];
    }else{
        if (![DeviceInfo checkIsIpad]) {
            [self.view scrollToY: 0];
        }
        [textField resignFirstResponder];
    }
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField {
    if (textField == _userTextField) {
        [_passTextField becomeFirstResponder];
    }else{
        [self.view scrollToY:0];
        [textField resignFirstResponder];
    }
    return NO;
}

- (void)checkLogin{
    
    [_hyLoglnButton StartAnimation];
    
    if ([CheckEmptyText checkEmptyText:_linkTextField]) {
        [self showTWMessageBarPleaseSelectValue];
        [_hyLoglnButton ErrorRevertAnimationCompletion:nil];
        return;
    }
    
    NSMutableDictionary * value = [NSMutableDictionary new];
    [value setValue:_userTextField.text forKey:@"loginName"];
    [value setValue:_passTextField.text forKey:@"password"];

    [[APIClient sharedClient] login:value
                         completion:^(LoginModel * loginModel, NSError * error, NSInteger statusCode) {
                             if (error != nil) {
                                 if (statusCode == 401) {
                                     [TWMessageBarManager sharedInstance].styleSheet = [TWMessageBarStyleSheet styleSheet];
                                     [[TWMessageBarManager sharedInstance] showMessageWithTitle:NSLocalizedString(@"ログイン失敗",nil)
                                                                                    description:NSLocalizedString(@"ユーザー名・暗証番号を確認してください",nil)
                                                                                           type:TWMessageBarMessageTypeError
                                                                                 statusBarStyle:UIStatusBarStyleLightContent
                                                                                       callback:nil];
                                 }else{
                                     [TWMessageBarManager sharedInstance].styleSheet = [TWMessageBarStyleSheet styleSheet];
                                     [[TWMessageBarManager sharedInstance] showMessageWithTitle:[NSString stringWithFormat:NSLocalizedString(@"ログインエラー: %lu",nil),statusCode]
                                                                                    description:NSLocalizedString(@"ユーザーが見つかりません",nil)
                                                                                           type:TWMessageBarMessageTypeError
                                                                                 statusBarStyle:UIStatusBarStyleLightContent
                                                                                       callback:nil];
                                 }
                                 [_hyLoglnButton ErrorRevertAnimationCompletion:nil];
                                 [_hyLoglnButton setTitleColor:[UIColor whiteColor] forState:UIControlStateNormal];
                             }
                             else{
                                 if (statusCode == 200) {
                                     //                                     NSLog(@"login Result: %@",loginModel);
                                     //                                     NSLog(@"User Token: %@",loginModel.userToken);
                                     
                                     [DeviceInfo saveLoginInformation:loginModel
                                                            loginName:_userTextField.text
                                                             password:_passTextField.text
                                                             position:0];
                                     
                                     // pushTokenの更新
                                     AppDelegate *delegate = (AppDelegate *)[[UIApplication sharedApplication] delegate];
                                     [delegate postFirebaseToken];
                                     
                                     dispatch_after(dispatch_time(DISPATCH_TIME_NOW, 0.5 * NSEC_PER_SEC), dispatch_get_main_queue(), ^{
                                         [self loginActive];
                                     });
                                 }else{
                                     [TWMessageBarManager sharedInstance].styleSheet = [TWMessageBarStyleSheet styleSheet];
                                     [[TWMessageBarManager sharedInstance] showMessageWithTitle:NSLocalizedString(@"ログイン失敗",nil)
                                                                                    description:NSLocalizedString(@"ユーザー名・暗証番号を確認してください",nil)
                                                                                           type:TWMessageBarMessageTypeError
                                                                                 statusBarStyle:UIStatusBarStyleLightContent
                                                                                       callback:nil];
                                     [_hyLoglnButton ErrorRevertAnimationCompletion:nil];
                                     [_hyLoglnButton setTitleColor:[UIColor whiteColor] forState:UIControlStateNormal];
                                 }
                             }
                         }];
}

#pragma mark - IBAction
- (void)signInOnClick:(HyLoglnButton*)button{
    
    [self checkLogin];
}

- (IBAction)prefectureOnClick:(id)sender{
    
    [_userTextField resignFirstResponder];
    [_passTextField resignFirstResponder];
    [self.view scrollToY: 0];

    dispatch_after(dispatch_time(DISPATCH_TIME_NOW, 0.25f * NSEC_PER_SEC), dispatch_get_main_queue(), ^{
        PopupPickerView *newView = [[[NSBundle mainBundle] loadNibNamed:@"PopupPickerView"
                                                                  owner:self
                                                                options:nil]objectAtIndex:0];

        NSDictionary * domainList = [DeviceInfo getDomainList];
        NSMutableArray *selectArray = [NSMutableArray array];

        NSArray *keys = [domainList allKeys];
        keys = [keys sortedArrayUsingComparator:^(id o1, id o2) {
            return [o1 compare:o2];
        }];


        [selectArray addObject:NSLocalizedString(@"選択してください",nil)];
        for (NSString *key in keys) {
            [selectArray addObject:key];
        }

        [newView setTheDataArray:selectArray
                           title:NSLocalizedString(@"チャンネル",nil)];
        
        [newView selectResult:^(NSString * result) {
            
        }];
        
        [newView closePopPickerViewOnClick:^(NSString * result) {
            if ([result isEqualToString:NSLocalizedString(@"選択してください",nil)] || [CheckEmptyText checkIsEmptyString:result]) {
                [self.view makeToast:NSLocalizedString(@"選択されていません",nil)
                            duration:2.5
                            position:CSToastPositionCenter];
                return;
            }

            for (NSString *key in domainList) {
                if ([result isEqualToString:key]){
                    [DeviceInfo setServerDomain:domainList[key]];
                }
            }
            _linkTextField.text = NSLocalizedString(result,nil);
        }];
    });
}

- (IBAction)backBtnOnClick:(id)sender {
    [self.navigationController popViewControllerAnimated:YES];
}

- (IBAction)forgetPasswordOnClick:(id)sender {
    KINWebBrowserViewController *webBrowser = [KINWebBrowserViewController webBrowser];
    [webBrowser loadURLString:@"https://kidsdiary.jp/password_forget"];
    webBrowser.showsPageTitleInNavigationBar = NO;
    webBrowser.tintColor = BASE_PURPLE;
    webBrowser.barTintColor = [UIColor whiteColor];
    [self.navigationController pushViewController:webBrowser animated:YES];
}


#pragma mark - HyLoglnButton
- (void)loginActive{
    __typeof(self) __weak weak = self;
    [weak LoginButton:_hyLoglnButton];
}

- (void)LoginButton:(HyLoglnButton *)button{
    __typeof(self) __weak weak = self;
    
    [button ExitAnimationCompletion:^{
        [weak didPresentControllerButtonTouch];
    }];
}

- (id<UIViewControllerAnimatedTransitioning>)animationControllerForPresentedController:(UIViewController *)presented
                                                                  presentingController:(UIViewController *)presenting sourceController:(UIViewController *)source{
    
    return [[HyTransitions alloc]initWithTransitionDuration:0.4f StartingAlpha:0.5f isBOOL:true];
}

- (id <UIViewControllerAnimatedTransitioning>)animationControllerForDismissedController:(UIViewController *)dismissed{
    
    return [[HyTransitions alloc]initWithTransitionDuration:0.4f StartingAlpha:0.8f isBOOL:false];
}

#pragma mark - NewViewController
- (void)didPresentControllerButtonTouch{
    UIStoryboard *storyboard;
    
    switch ([DeviceInfo getUserType]) {
        case GUARDIAN:
            if ([DeviceInfo getDiyInterfaces].count > 0) {
                storyboard = [UIStoryboard storyboardWithName:[DeviceInfo checkIsIpad]? @"DIYParentScreen_iPad" : @"DIYParentScreen" bundle:nil];
            }
            else if ([[DeviceInfo getInterfaceType] isEqualToString:@"SIMPLE_1"] || [DeviceInfo isRunningTargetJukuNote]) {
                storyboard = [UIStoryboard storyboardWithName:[DeviceInfo checkIsIpad]? @"TuitionScreen_iPad" : @"TuitionScreen" bundle:nil];
            }
            else{
                storyboard = [UIStoryboard storyboardWithName:[DeviceInfo checkIsIpad]? @"ParentScreen_iPad" : @"ParentScreen" bundle:nil];
            }
            break;
        case TEACHER:
            storyboard = [UIStoryboard storyboardWithName:[DeviceInfo checkIsIpad]? @"TeacherScreen_iPad":@"TeacherScreen" bundle:nil];
            break;
        case DIRECTOR:
            storyboard = [UIStoryboard storyboardWithName:[DeviceInfo checkIsIpad]? @"DirectorScreen_iPad" : @"DirectorScreen" bundle:nil];
            break;
        default:
            break;
    }
    
    RootViewController *newView = (RootViewController *)[storyboard instantiateInitialViewController];
    [[UIApplication sharedApplication].delegate window].rootViewController  = newView;
    
}
@end
