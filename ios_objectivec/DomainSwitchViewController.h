//
//  DomainSwitchViewController.h
//  KidsDiary
//
//  Created by FukudaAkali on 29/05/2019.
//  Copyright © 2019 **********. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "ACFloatingTextField.h"

@interface DomainSwitchViewController : BaseViewController

@property (weak, nonatomic) IBOutlet UIVisualEffectView *bgView;
@property (weak, nonatomic) IBOutlet UIButton *prefectureBtn;
@property (weak, nonatomic) IBOutlet UIView *loginBtnView;
@property (weak, nonatomic) IBOutlet UIImageView *backArrowIcon;
@property (weak, nonatomic) IBOutlet UIImageView *downArrowIcon;
@property (weak, nonatomic) IBOutlet ACFloatingTextField *userTextField;
@property (weak, nonatomic) IBOutlet ACFloatingTextField *passTextField;
@property (weak, nonatomic) IBOutlet ACFloatingTextField *linkTextField;
@property (weak, nonatomic) IBOutlet UIButton *backBtn;
@property (weak, nonatomic) IBOutlet UIButton *forgetBtn;

- (IBAction)prefectureOnClick:(id)sender;
- (IBAction)backBtnOnClick:(id)sender;
- (IBAction)forgetPasswordOnClick:(id)sender;

@end
